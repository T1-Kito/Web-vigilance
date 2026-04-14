<?php

namespace App\Console\Commands;

use App\Models\CompetitorPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SyncVuHoangTelecomPrices extends Command
{
    protected $signature = 'competitor:sync-vuhoangtelecom
                            {--url=* : Danh sách URL danh mục cần quét}
                            {--limit=1200 : Số link sản phẩm tối đa sẽ xử lý}
                            {--max-pages=30 : Số trang danh mục tối đa mỗi URL}
                            {--competitor=vuhoangtelecom.vn : Tên đối thủ lưu vào DB}
                            {--reset : Xoá dữ liệu cũ của đối thủ trước khi đồng bộ}';

    protected $description = 'Đồng bộ giá đối thủ từ vuhoangtelecom.vn vào competitor_prices';

    public function handle(): int
    {
        $urls = collect((array) $this->option('url'))
            ->map(fn ($u) => trim((string) $u))
            ->filter()
            ->values();

        if ($urls->isEmpty()) {
            $this->warn('Bạn cần truyền ít nhất 1 URL danh mục bằng --url=...');
            return self::FAILURE;
        }

        $competitor = trim((string) $this->option('competitor')) ?: 'vuhoangtelecom.vn';
        $limit = max(1, (int) $this->option('limit'));
        $maxPages = max(1, min(100, (int) $this->option('max-pages')));

        if ((bool) $this->option('reset')) {
            $deleted = CompetitorPrice::query()
                ->where('competitor_name', $competitor)
                ->delete();
            $this->warn('Đã xoá ' . $deleted . ' dòng cũ của ' . $competitor);
        }

        $productLinks = collect();

        foreach ($urls as $categoryUrl) {
            $categoryLinks = $this->extractCategoryProductLinks($categoryUrl, $maxPages);
            $productLinks = $productLinks->merge($categoryLinks);
            $this->line("- {$categoryUrl}: tìm thấy " . $categoryLinks->count() . ' link sản phẩm');
        }

        $productLinks = $productLinks->unique()->values()->take($limit);

        if ($productLinks->isEmpty()) {
            $this->warn('Không tìm thấy link sản phẩm nào từ các URL đã truyền.');
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

        $this->info('Hoàn tất. Đã lưu ' . $saved . ' bản ghi giá đối thủ từ ' . $competitor . '.');

        return self::SUCCESS;
    }

    private function extractCategoryProductLinks(string $categoryUrl, int $maxPages): Collection
    {
        $all = collect();
        $emptyStreak = 0;

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $this->buildCategoryPageUrl($categoryUrl, $page);
            $this->line("  > Quét trang {$page}/{$maxPages}: {$url}");
            $html = $this->fetchHtml($url);

            if ($html === '') {
                $this->warn("  ! Không lấy được HTML trang {$page}");
                $emptyStreak++;
                if ($emptyStreak >= 2) {
                    break;
                }
                continue;
            }

            $links = $this->extractProductLinksFromCategoryHtml($html);
            $this->line('    + Link sản phẩm trang này: ' . $links->count());

            if ($links->isEmpty()) {
                $emptyStreak++;
                if ($emptyStreak >= 2) {
                    break;
                }
            } else {
                $emptyStreak = 0;
                $all = $all->merge($links);
            }
        }

        return $all->unique()->values();
    }

    private function buildCategoryPageUrl(string $baseUrl, int $page): string
    {
        if ($page <= 1) {
            return $baseUrl;
        }

        $clean = rtrim($baseUrl, '/');
        return $clean . '/page/' . $page . '/';
    }

    private function fetchHtml(string $url): string
    {
        try {
            $res = Http::timeout(25)
                ->connectTimeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
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

    private function extractProductLinksFromCategoryHtml(string $html): Collection
    {
        // Link product chính của block .az-product-item
        preg_match_all('/<div[^>]*class="[^"]*az-product-item[^"]*"[\s\S]*?<a[^>]+href=["\']([^"\']+)["\'][^>]*class="[^"]*(?:az-box-product-des|nt-img)[^"]*"/iu', $html, $matches);

        return collect($matches[1] ?? [])
            ->map(function ($href) {
                $href = html_entity_decode((string) $href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return trim($href);
            })
            ->filter(function ($url) {
                if (!is_string($url) || $url === '') return false;
                $u = strtolower($url);
                if (!str_starts_with($u, 'http')) return false;
                if (!str_contains($u, 'vuhoangtelecom.vn')) return false;
                if (!str_contains($u, '/san-pham/')) return false;
                return true;
            })
            ->values();
    }

    private function extractNameAndPrice(string $html): array
    {
        $name = '';
        $price = 0.0;
        $noPublicPrice = false;

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $mName)) {
            $name = trim(strip_tags((string) ($mName[1] ?? '')));
        }

        // Ưu tiên tuyệt đối: lấy giá từ JSON-LD Product (WooCommerce)
        $jsonLdPrice = $this->extractPriceFromJsonLd($html);
        if ($jsonLdPrice > 0) {
            $price = $jsonLdPrice;
        }

        // Fallback 1: lấy trong block chính gần tên + mã SP
        if ($price <= 0 && preg_match('/Mã\s*SP\s*:[\s\S]{0,600}?([0-9]{1,3}(?:[\.,][0-9]{3}){1,4})\s*(?:đ|vnd|vnđ)/iu', $html, $mNearSkuPrice)) {
            $price = $this->toPrice($mNearSkuPrice[1] ?? '');
        }

        // Fallback 2: block az-price chi tiết
        if ($price <= 0 && preg_match('/<div[^>]*class="[^"]*az-price[^"]*"[^>]*>([\s\S]*?)<\/div>/iu', $html, $mPriceBlock)) {
            $priceBlock = (string) ($mPriceBlock[1] ?? '');

            if (preg_match('/<p[^>]*>\s*([0-9][0-9\.,\s]{1,20})\s*<span[^>]*class="[^"]*unit-price[^"]*"/iu', $priceBlock, $mP)) {
                $price = $this->toPrice($mP[1] ?? '');
            }

            if ($price <= 0 && preg_match('/<ins[^>]*>\s*([0-9][0-9\.,\s]{1,20})\s*(?:đ|vnd|vnđ)?\s*<\/ins>/iu', $priceBlock, $mIns)) {
                $price = $this->toPrice($mIns[1] ?? '');
            }
        }

        // Fallback 3: chỉ lấy tiền ngay sau tiêu đề H1 để tránh dính sản phẩm liên quan
        if ($price <= 0) {
            $h1Pos = mb_stripos($html, '</h1>', 0, 'UTF-8');
            if ($h1Pos !== false) {
                $tail = mb_substr($html, $h1Pos, 2500, 'UTF-8');
                if (preg_match('/([0-9]{1,3}(?:[\.,][0-9]{3}){1,4})\s*(?:đ|vnd|vnđ)/iu', $tail, $mTailPrice)) {
                    $price = $this->toPrice($mTailPrice[1] ?? '');
                }
            }
        }

        if ($price <= 0 && preg_match('/(liên\s*hệ|vui\s*lòng\s*gọi|call\s*for\s*price)/iu', $html)) {
            $noPublicPrice = true;
        }

        if ($price > 0 && ($price < 1000 || $price > 1000000000)) {
            $price = 0;
        }

        if ($noPublicPrice) {
            $price = 0;
        }

        return ['name' => $name, 'price' => $price, 'no_public_price' => $noPublicPrice];
    }

    private function extractPriceFromJsonLd(string $html): float
    {
        if (!preg_match_all('/<script[^>]*type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/iu', $html, $mScripts)) {
            return 0;
        }

        foreach (($mScripts[1] ?? []) as $rawJson) {
            $rawJson = trim((string) $rawJson);
            if ($rawJson === '') {
                continue;
            }

            $decoded = json_decode($rawJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            $price = $this->findPriceInJsonLdNode($decoded);
            if ($price > 0) {
                return $price;
            }
        }

        return 0;
    }

    private function findPriceInJsonLdNode(mixed $node): float
    {
        if (is_array($node)) {
            // Node Product thường có offers.price
            if (isset($node['@type']) && is_string($node['@type']) && stripos($node['@type'], 'Product') !== false) {
                $offers = $node['offers'] ?? null;
                if (is_array($offers)) {
                    if (isset($offers['price'])) {
                        $p = $this->toPrice($offers['price']);
                        if ($p > 0) {
                            return $p;
                        }
                    }

                    if (array_is_list($offers)) {
                        foreach ($offers as $offerItem) {
                            if (is_array($offerItem) && isset($offerItem['price'])) {
                                $p = $this->toPrice($offerItem['price']);
                                if ($p > 0) {
                                    return $p;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($node as $child) {
                $p = $this->findPriceInJsonLdNode($child);
                if ($p > 0) {
                    return $p;
                }
            }
        }

        return 0;
    }

    private function normalizeKey(?string $value): string
    {
        $v = trim((string) $value);
        if ($v === '') return '';

        $v = mb_strtolower($v, 'UTF-8');
        $v = preg_replace('/["\'`]+/u', '', $v) ?? $v;
        $v = preg_replace('/[^\p{L}\p{N}\s\-]+/u', ' ', $v) ?? $v;
        $v = preg_replace('/\s+/u', ' ', $v) ?? $v;

        return trim($v);
    }

    private function extractProductCode(string $name): string
    {
        // Ưu tiên mã model có ít nhất 1 dấu gạch hoặc có độ dài đủ lớn để tránh match nhầm ngắn như "h6c"
        if (preg_match('/\b([a-z]{1,8}[\-\/][a-z0-9\-\/]{2,})\b/iu', $name, $m1)) {
            return trim((string) ($m1[1] ?? ''));
        }

        if (preg_match('/\b([a-z]{2,8}[\-\s]?[0-9]{2,8}[a-z0-9\-]{0,20})\b/iu', $name, $m2)) {
            $code = trim((string) ($m2[1] ?? ''));
            if (mb_strlen($code, 'UTF-8') >= 6) {
                return $code;
            }
        }

        return '';
    }

    private function toPrice(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);
        return $digits !== '' ? (float) $digits : 0;
    }
}
