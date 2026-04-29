<?php

namespace App\Console\Commands;

use App\Models\CompetitorPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncSieuThiVienThongPrices extends Command
{
    protected $signature = 'competitor:sync-sieuthivienthong
                            {--url=* : Danh sách URL danh mục cần quét}
                            {--limit=120 : Số link sản phẩm tối đa sẽ xử lý}
                            {--competitor=sieuthivienthong.com : Tên đối thủ lưu vào DB}
                            {--reset : Xoá dữ liệu cũ của đối thủ trước khi đồng bộ}';

    protected $description = 'Đồng bộ giá đối thủ từ sieuthivienthong.com vào competitor_prices';

    public function handle(): int
    {
        $urls = collect((array) $this->option('url'))
            ->map(fn ($u) => trim((string) $u))
            ->filter()
            ->values();

        if ($urls->isEmpty()) {
            $urls = collect([
                'https://sieuthivienthong.com/',
            ]);
        }

        $competitor = trim((string) $this->option('competitor')) ?: 'sieuthivienthong.com';
        $limit = max(1, (int) $this->option('limit'));

        if ((bool) $this->option('reset')) {
            $deleted = CompetitorPrice::query()
                ->where('competitor_name', $competitor)
                ->delete();
            $this->warn('Đã xoá ' . $deleted . ' dòng cũ của ' . $competitor);
        }

        $this->info('Bắt đầu quét: ' . $competitor);

        $productLinks = collect();
        foreach ($urls as $url) {
            $html = $this->fetchHtml($url);
            if ($html === '') {
                $this->warn('Không đọc được URL: ' . $url);
                continue;
            }

            $links = $this->extractProductLinks($html, $url);
            $ajaxLinks = $this->extractAjaxProductLinks($url);

            $allLinks = $links->merge($ajaxLinks)->unique()->values();
            $productLinks = $productLinks->merge($allLinks);

            $this->line("- {$url}: thường=" . $links->count() . ', ajax=' . $ajaxLinks->count() . ', tổng=' . $allLinks->count() . ' link');
        }

        $productLinks = $productLinks->unique()->values()->take($limit);

        if ($productLinks->isEmpty()) {
            $this->warn('Không tìm thấy link sản phẩm nào. Bạn thử truyền URL danh mục cụ thể bằng --url=...');
            return self::FAILURE;
        }

        $saved = 0;
        $now = now();

        foreach ($productLinks as $link) {
            $html = $this->fetchHtml($link);
            if ($html === '') {
                continue;
            }

            $parsed = $this->extractNameAndPrice($html);
            $name = trim((string) ($parsed['name'] ?? ''));
            $price = (float) ($parsed['price'] ?? 0);
            $noPublicPrice = (bool) ($parsed['no_public_price'] ?? false);

            if ($name === '') {
                continue;
            }

            $keys = collect([$this->normalizeKey($name)]);
            $code = $this->extractProductCode($name);
            if ($code !== '') {
                $keys->push($this->normalizeKey($code));
            }

            foreach ($keys->filter()->unique()->values() as $key) {
                $latest = CompetitorPrice::query()
                    ->where('competitor_name', $competitor)
                    ->where('product_key', $key)
                    ->orderByDesc('checked_at')
                    ->orderByDesc('id')
                    ->first(['price', 'product_url']);

                $samePrice = $latest && (float) $latest->price === (float) $price;
                $sameUrl = $latest && (string) ($latest->product_url ?? '') === (string) $link;

                if ($samePrice && $sameUrl) {
                    continue;
                }

                CompetitorPrice::create([
                    'competitor_name' => $competitor,
                    'product_key' => $key,
                    'product_name_raw' => $name,
                    'price' => $price,
                    'product_url' => $link,
                    'checked_at' => $now,
                ]);
                $saved++;
            }

            if ($noPublicPrice || $price <= 0) {
                $this->line("• {$name} => Web không để giá");
            } else {
                $this->line("✓ {$name} => " . number_format($price, 0, ',', '.') . 'đ');
            }
        }

        $this->info('Hoàn tất. Đã lưu ' . $saved . ' bản ghi giá đối thủ.');

        return self::SUCCESS;
    }

    private function fetchHtml(string $url): string
    {
        try {
            $res = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                ])
                ->get($url);

            if (!$res->ok()) {
                return '';
            }

            return (string) $res->body();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function extractAjaxProductLinks(string $categoryUrl): \Illuminate\Support\Collection
    {
        $parts = parse_url($categoryUrl);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return collect();
        }

        $base = $parts['scheme'] . '://' . $parts['host'];
        $path = (string) ($parts['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }

        $ajaxBase = $base . '/index.php';

        $resultLinks = collect();
        $maxPages = 30;

        for ($pg = 2; $pg <= $maxPages; $pg++) {
            try {
                $res = Http::timeout(20)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0 Safari/537.36',
                        'Accept' => 'application/json, text/plain, */*',
                        'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Referer' => $categoryUrl,
                    ])
                    ->get($ajaxBase, [
                        'page' => 'ajax',
                        'type' => 'load-articles',
                        'pg' => $pg,
                        'sort' => 'popular',
                        'url' => $path,
                    ]);

                if (!$res->ok()) {
                    break;
                }

                $json = $res->json();
                if (!is_array($json)) {
                    break;
                }

                $dataHtml = (string) ($json['data'] ?? '');
                if ($dataHtml === '') {
                    break;
                }

                $pageLinks = $this->extractProductLinks($dataHtml, $base);
                $resultLinks = $resultLinks->merge($pageLinks);

                $hasMore = (bool) ($json['hasMore'] ?? false);
                if (!$hasMore) {
                    break;
                }
            } catch (\Throwable $e) {
                break;
            }
        }

        return $resultLinks->unique()->values();
    }

    private function extractProductLinks(string $html, string $baseUrl): \Illuminate\Support\Collection
    {
        preg_match_all('/href=["\']([^"\']+)["\']/i', $html, $matches);
        $links = collect($matches[1] ?? [])
            ->map(function ($href) use ($baseUrl) {
                $href = html_entity_decode((string) $href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (str_starts_with($href, '//')) {
                    return 'https:' . $href;
                }
                if (str_starts_with($href, '/')) {
                    $parts = parse_url($baseUrl);
                    if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
                        return null;
                    }
                    return $parts['scheme'] . '://' . $parts['host'] . $href;
                }
                return $href;
            })
            ->filter(function ($url) {
                if (!is_string($url) || $url === '') return false;
                $u = strtolower($url);
                if (!str_starts_with($u, 'http')) return false;
                if (!str_contains($u, 'sieuthivienthong.com')) return false;

                if (
                    str_contains($u, '/giomuahang') ||
                    str_contains($u, '/contact') ||
                    str_contains($u, '/info') ||
                    str_contains($u, '/policy') ||
                    str_contains($u, '/rss') ||
                    str_contains($u, '/home') ||
                    str_contains($u, '/discount') ||
                    str_contains($u, '#')
                ) {
                    return false;
                }

                // Link sản phẩm của site này thường có dạng: /ten-san-pham-12345.html
                return (bool) preg_match('/\-[0-9]{4,}\.html$/i', $u);
            })
            ->values();

        return $links;
    }

    private function extractNameAndPrice(string $html): array
    {
        $name = '';
        $price = 0.0;
        $noPublicPrice = false;

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $mName)) {
            $name = trim(strip_tags((string) ($mName[1] ?? '')));
        }

        if (preg_match('/<(?:div|h[1-6]|p|span)[^>]*class="[^"]*price[^"]*"[^>]*>([\s\S]*?)<\/(?:div|h[1-6]|p|span)>/iu', $html, $mPriceBlock)) {
            $priceBlock = (string) ($mPriceBlock[1] ?? '');

            if (preg_match('/<strong[^>]*>\s*([0-9]{1,3}(?:\.[0-9]{3}){1,4}(?:\,[0-9]+)?)\s*(?:đ|vnđ|vnd)?\s*<\/strong>/iu', $priceBlock, $mPriceStrong)) {
                $digits = preg_replace('/[^0-9]/', '', (string) ($mPriceStrong[1] ?? ''));
                $price = $digits !== '' ? (float) $digits : 0;
            }

            if ($price <= 0 && preg_match('/<span[^>]*>\s*([0-9]{1,3}(?:\.[0-9]{3}){1,4}(?:\,[0-9]+)?)\s*(?:đ|vnđ|vnd)?\s*<\/span>/iu', $priceBlock, $mPriceSpan)) {
                $digits = preg_replace('/[^0-9]/', '', (string) ($mPriceSpan[1] ?? ''));
                $price = $digits !== '' ? (float) $digits : 0;
            }

            if ($price <= 0 && preg_match('/(vui\s*l[oò]ng\s*g[oọ]i|li[eê]n\s*h[eệ]|call\s*for\s*price)/iu', $priceBlock)) {
                $noPublicPrice = true;
            }
        }

        // Fallback chắc chắn theo vùng thông tin chính: sau "Mã SP" sẽ đến giá sản phẩm chính.
        if ($price <= 0) {
            if (preg_match('/M[ãa]\s*SP\s*:\s*[^\n\r<]{1,40}[\s\S]{0,500}?([0-9]{1,3}(?:\.[0-9]{3}){1,4})\s*VND/iu', $html, $mPriceAfterSku)) {
                $digits = preg_replace('/[^0-9]/', '', (string) ($mPriceAfterSku[1] ?? ''));
                $price = $digits !== '' ? (float) $digits : 0;
            }
        }

        if ($price <= 0 && preg_match('/M[ãa]\s*SP\s*:\s*[^\n\r<]{1,40}[\s\S]{0,400}?(vui\s*l[oò]ng\s*g[oọ]i|li[eê]n\s*h[eệ])/iu', $html)) {
            $noPublicPrice = true;
        }

        if (preg_match_all('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $jsonMatches)) {
            foreach ($jsonMatches[1] as $jsonText) {
                $jsonText = trim((string) $jsonText);
                if ($jsonText === '') continue;

                $decoded = json_decode($jsonText, true);
                if (json_last_error() !== JSON_ERROR_NONE) continue;

                $candidate = $this->findProductInJsonLd($decoded);
                if (!$candidate) continue;

                if ($name === '') {
                    $name = trim((string) ($candidate['name'] ?? ''));
                }

                if ($price <= 0) {
                    $p = $this->extractPriceFromProductJsonLd($candidate);
                    if ($p > 0) {
                        $price = $p;
                    }
                }

                if ($name !== '' && ($price > 0 || $noPublicPrice)) {
                    break;
                }
            }
        }

        if ($price > 0 && ($price < 10000 || $price > 50000000)) {
            $price = 0;
        }

        if ($noPublicPrice) {
            $price = 0;
        }

        return ['name' => $name, 'price' => $price, 'no_public_price' => $noPublicPrice];
    }

    private function findProductInJsonLd(mixed $decoded): ?array
    {
        if (is_array($decoded)) {
            if (($decoded['@type'] ?? null) === 'Product') {
                return $decoded;
            }

            foreach ($decoded as $item) {
                $found = $this->findProductInJsonLd($item);
                if ($found) return $found;
            }
        }

        return null;
    }

    private function extractPriceFromProductJsonLd(array $product): float
    {
        $offers = $product['offers'] ?? null;

        if (is_array($offers) && array_key_exists('price', $offers)) {
            return $this->toPrice($offers['price']);
        }

        if (is_array($offers)) {
            foreach ($offers as $offer) {
                if (is_array($offer) && array_key_exists('price', $offer)) {
                    $p = $this->toPrice($offer['price']);
                    if ($p > 0) return $p;
                }
            }
        }

        return 0;
    }

    private function toPrice(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);
        return $digits !== '' ? (float) $digits : 0;
    }

    private function normalizeKey(?string $value): string
    {
        $v = trim((string) $value);
        if ($v === '') return '';

        $v = mb_strtolower($v, 'UTF-8');
        $v = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $v) ?? $v;
        $v = preg_replace('/\s+/u', ' ', $v) ?? $v;

        return trim($v);
    }

    private function extractProductCode(string $name): string
    {
        if (preg_match('/\b([a-z]{1,5}[\-\s]?[0-9]{1,5}[a-z0-9\-]*)\b/iu', $name, $m)) {
            return trim((string) ($m[1] ?? ''));
        }

        return '';
    }
}
