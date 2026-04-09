<?php

namespace App\Support;

use ZipArchive;

class DocxTemplateRenderer
{
    public static function renderToTempFile(string $templatePath, array $data, array $items = []): string
    {
        $tempPath = storage_path('app/tmp/template-' . uniqid('', true) . '.docx');
        if (!is_dir(dirname($tempPath))) {
            @mkdir(dirname($tempPath), 0777, true);
        }

        copy($templatePath, $tempPath);

        $zip = new ZipArchive();
        if ($zip->open($tempPath) !== true) {
            throw new \RuntimeException('Không mở được file template DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new \RuntimeException('Template DOCX không hợp lệ (thiếu document.xml).');
        }

        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml === false) {
            $zip->close();
            throw new \RuntimeException('Template DOCX không hợp lệ (thiếu document.xml.rels).');
        }

        $mediaToAdd = [];
        $ridSeed = self::nextRidSeed($relsXml);

        // Ưu tiên lặp theo hàng bảng để tránh vỡ layout
        [$xml, $mediaToAdd, $ridSeed] = self::replaceItemsBlockByTableRows($xml, $items, $mediaToAdd, $ridSeed);

        // Fallback block thường
        if (str_contains($xml, '{{#Items}}') && str_contains($xml, '{{/Items}}')) {
            [$xml, $mediaToAdd, $ridSeed] = self::replaceItemsBlock($xml, $items, $mediaToAdd, $ridSeed);
        }

        $xml = self::replaceScalarPlaceholders($xml, $data);
        $xml = preg_replace('/\{\{[^\}]+\}\}/', '', $xml) ?: $xml;

        // Replace luôn trong header/footer (nơi thường đặt Số:, Ngày:)
        for ($i = 1; $i <= 10; $i++) {
            $headerName = 'word/header' . $i . '.xml';
            $h = $zip->getFromName($headerName);
            if ($h !== false) {
                $h = self::replaceScalarPlaceholders($h, $data);
                $h = preg_replace('/\{\{[^\}]+\}\}/', '', $h) ?: $h;
                $zip->addFromString($headerName, $h);
            }

            $footerName = 'word/footer' . $i . '.xml';
            $f = $zip->getFromName($footerName);
            if ($f !== false) {
                $f = self::replaceScalarPlaceholders($f, $data);
                $f = preg_replace('/\{\{[^\}]+\}\}/', '', $f) ?: $f;
                $zip->addFromString($footerName, $f);
            }
        }

        // Ghi media + relationship cho ảnh vừa chèn
        foreach ($mediaToAdd as $m) {
            $zip->addFile($m['path'], 'word/' . $m['target']);
            $relsXml = self::appendRelationship($relsXml, $m['rid'], $m['target']);
        }

        $zip->addFromString('word/document.xml', $xml);
        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);
        $zip->close();

        return $tempPath;
    }

    private static function replaceScalarPlaceholders(string $xml, array $data): string
    {
        // 1) Replace trực tiếp khi token liền mạch
        foreach ($data as $key => $value) {
            $safe = self::escapeXml((string) ($value ?? ''));
            $p1 = '/\{\{\s*' . preg_quote((string) $key, '/') . '\s*\}\}/u';
            $p2 = '/\{\{\{\s*' . preg_quote((string) $key, '/') . '\s*\}\}\}/u';
            $xml = preg_replace($p1, $safe, $xml) ?: $xml;
            $xml = preg_replace($p2, $safe, $xml) ?: $xml;
        }

        // 2) Replace token bị Word tách run: {{|QuoteCode|}}
        foreach ($data as $key => $value) {
            $safe = self::escapeXml((string) ($value ?? ''));
            $escapedKey = preg_quote((string) $key, '/');

            $splitDouble = '/\{\{\s*(?:<\/w:t><w:t[^>]*>)?\s*' . $escapedKey . '\s*(?:<\/w:t><w:t[^>]*>)?\s*\}\}/u';
            $splitTriple = '/\{\{\{\s*(?:<\/w:t><w:t[^>]*>)?\s*' . $escapedKey . '\s*(?:<\/w:t><w:t[^>]*>)?\s*\}\}\}/u';

            $xml = preg_replace($splitDouble, $safe, $xml) ?: $xml;
            $xml = preg_replace($splitTriple, $safe, $xml) ?: $xml;
        }

        return $xml;
    }

    private static function replaceItemsBlockByTableRows(string $xml, array $items, array $mediaToAdd, int $ridSeed): array
    {
        if (!preg_match_all('/<w:tr\b[\s\S]*?<\/w:tr>/', $xml, $rows, PREG_OFFSET_CAPTURE)) {
            return [$xml, $mediaToAdd, $ridSeed];
        }

        $startIdx = null;
        $endIdx = null;
        $rowList = $rows[0];

        foreach ($rowList as $idx => $rowMatch) {
            $rowXml = $rowMatch[0];
            if ($startIdx === null && str_contains($rowXml, '{{#Items}}')) {
                $startIdx = $idx;
            }
            if ($startIdx !== null && str_contains($rowXml, '{{/Items}}')) {
                $endIdx = $idx;
                break;
            }
        }

        if ($startIdx === null || $endIdx === null || $endIdx < $startIdx) {
            return [$xml, $mediaToAdd, $ridSeed];
        }

        $blockRows = array_slice($rowList, $startIdx, $endIdx - $startIdx + 1);

        $repeatableRows = array_values(array_filter($blockRows, function ($row) {
            $r = $row[0] ?? '';
            return str_contains($r, '{{Item.') || str_contains($r, '{{#Items}}') || str_contains($r, '{{/Items}}');
        }));
        if (empty($repeatableRows)) {
            $repeatableRows = $blockRows;
        }

        $built = '';
        $rowsToRender = !empty($items) ? $items : [[]];

        foreach ($rowsToRender as $item) {
            foreach ($repeatableRows as $rowMatch) {
                $rowXml = $rowMatch[0];
                $rowXml = str_replace(['{{#Items}}', '{{/Items}}'], '', $rowXml);

                foreach ($item as $k => $v) {
                    if ($k === 'Image') {
                        continue;
                    }
                    if (is_scalar($v) || $v === null) {
                        $rowXml = str_replace('{{Item.' . $k . '}}', self::escapeXml((string) ($v ?? '')), $rowXml);
                    }
                }

                if (str_contains($rowXml, '{{Item.Image}}')) {
                    $imgPath = (string) ($item['Image'] ?? '');
                    if ($imgPath !== '' && is_file($imgPath)) {
                        $rid = 'rIdImg' . $ridSeed++;
                        $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'png');
                        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'], true)) {
                            $ext = 'png';
                        }
                        $target = 'media/generated-' . uniqid('', true) . '.' . $ext;
                        $mediaToAdd[] = ['rid' => $rid, 'target' => $target, 'path' => $imgPath];
                        $rowXml = str_replace('{{Item.Image}}', self::drawingXml($rid), $rowXml);
                    } else {
                        $rowXml = str_replace('{{Item.Image}}', '', $rowXml);
                    }
                }

                $rowXml = preg_replace('/\{\{\s*Item\.[^\}]+\}\}/', '', $rowXml) ?: $rowXml;
                $built .= $rowXml;
            }
        }

        $startPos = $blockRows[0][1];
        $endPos = $blockRows[count($blockRows) - 1][1] + strlen($blockRows[count($blockRows) - 1][0]);

        $xml = substr($xml, 0, $startPos) . $built . substr($xml, $endPos);
        return [$xml, $mediaToAdd, $ridSeed];
    }

    private static function replaceItemsBlock(string $xml, array $items, array $mediaToAdd, int $ridSeed): array
    {
        if (!preg_match('/\{\{#Items\}\}(.*?)\{\{\/Items\}\}/s', $xml, $m)) {
            return [$xml, $mediaToAdd, $ridSeed];
        }

        $block = $m[1] ?? '';
        $result = '';

        foreach (($items ?: [[]]) as $row) {
            $rowXml = $block;
            foreach ($row as $k => $v) {
                if ($k === 'Image') {
                    continue;
                }
                if (is_scalar($v) || $v === null) {
                    $rowXml = str_replace('{{Item.' . $k . '}}', self::escapeXml((string) ($v ?? '')), $rowXml);
                }
            }

            if (str_contains($rowXml, '{{Item.Image}}')) {
                $imgPath = (string) ($row['Image'] ?? '');
                if ($imgPath !== '' && is_file($imgPath)) {
                    $rid = 'rIdImg' . $ridSeed++;
                    $ext = strtolower(pathinfo($imgPath, PATHINFO_EXTENSION) ?: 'png');
                    $target = 'media/generated-' . uniqid('', true) . '.' . $ext;
                    $mediaToAdd[] = ['rid' => $rid, 'target' => $target, 'path' => $imgPath];
                    $rowXml = str_replace('{{Item.Image}}', self::drawingXml($rid), $rowXml);
                } else {
                    $rowXml = str_replace('{{Item.Image}}', '', $rowXml);
                }
            }

            $rowXml = preg_replace('/\{\{\s*Item\.[^\}]+\}\}/', '', $rowXml) ?: $rowXml;
            $result .= $rowXml;
        }

        $xml = preg_replace('/\{\{#Items\}\}(.*?)\{\{\/Items\}\}/s', $result, $xml, 1) ?: $xml;
        return [$xml, $mediaToAdd, $ridSeed];
    }

    private static function drawingXml(string $rid): string
    {
        return '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"><wp:extent cx="685800" cy="685800"/><wp:docPr id="1" name="Picture"/><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="0" name="img"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="' . $rid . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="685800" cy="685800"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>';
    }

    private static function appendRelationship(string $relsXml, string $rid, string $target): string
    {
        $rel = '<Relationship Id="' . $rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="' . $target . '"/>';
        return str_replace('</Relationships>', $rel . '</Relationships>', $relsXml);
    }

    private static function nextRidSeed(string $relsXml): int
    {
        preg_match_all('/Id="rId(?:Img)?(\d+)"/', $relsXml, $m);
        $nums = array_map('intval', $m[1] ?? []);
        return empty($nums) ? 1000 : (max($nums) + 1);
    }

    private static function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
