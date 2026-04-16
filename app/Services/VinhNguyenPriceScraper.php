<?php

namespace App\Services;

use App\Models\CompetitorPrice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VinhNguyenPriceScraper
{
    private string $baseUrl = 'https://vinhnguyen.vn';
    private string $categoryUrl = 'https://vinhnguyen.vn/may-cham-cong.html';
    private string $competitorName = 'vinh-nguyen';

    /**
     * Scrape the category pages and upsert competitor price rows.
     *
     * @return array{ok:bool,count:int,items:int,errors:int,message:string}
     */
    public function sync(): array
    {
        $allItems = [];
        $errors = 0;
        $seenUrls = [];
        $maxPages = 20;

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $page === 1 ? $this->categoryUrl : $this->categoryUrl . '?page=' . $page;
            $response = Http::timeout(60)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
                ])
                ->get($url);

            if (!$response->ok()) {
                break;
            }

            $html = $response->body();
            $items = $this->extractProducts($html);

            if (empty($items)) {
                break;
            }

            $newOnPage = 0;
            foreach ($items as $item) {
                if (isset($seenUrls[$item['product_url']])) {
                    continue;
                }
                $seenUrls[$item['product_url']] = true;
                $allItems[] = $item;
                $newOnPage++;
            }

            // Stop if the page doesn't introduce anything new.
            if ($newOnPage === 0) {
                break;
            }
        }

        $saved = 0;
        foreach ($allItems as $item) {
            try {
                CompetitorPrice::updateOrCreate(
                    [
                        'competitor_name' => $this->competitorName,
                        'product_key' => $item['product_key'],
                    ],
                    [
                        'product_name_raw' => $item['product_name_raw'],
                        'price' => $item['price'],
                        'product_url' => $item['product_url'],
                        'checked_at' => now(),
                    ]
                );
                $saved++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        return [
            'ok' => true,
            'count' => $saved,
            'items' => count($allItems),
            'errors' => $errors,
            'message' => $saved > 0
                ? "Đã quét và lưu {$saved} sản phẩm từ Vinh Nguyễn."
                : 'Không tìm thấy sản phẩm nào để lưu.',
        ];
    }

    /**
     * @return array<int,array{product_key:string,product_name_raw:string,price:float,product_url:string}>
     */
    private function extractProducts(string $html): array
    {
        $results = [];

        // Try to parse product blocks with product links and nearby price.
        if (preg_match_all('~<a[^>]+href="(?P<href>https?://vinhnguyen\.vn/[^"\']+)"[^>]*>(?P<inner>.*?)</a>~si', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $href = html_entity_decode($m['href'], ENT_QUOTES | ENT_HTML5);
                $inner = $m['inner'];
                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5));
                if ($text === '' || mb_strlen($text) < 8) {
                    continue;
                }

                if (!$this->looksLikeProductTitle($text)) {
                    continue;
                }

                $price = $this->extractPriceNearLink($html, $href, $text);
                if ($price <= 0) {
                    continue;
                }

                $results[$href] = [
                    'product_key' => $this->makeProductKey($href, $text),
                    'product_name_raw' => $text,
                    'price' => $price,
                    'product_url' => $href,
                ];

                if (count($results) >= 120) {
                    break;
                }
            }
        }

        return array_values($results);
    }

    private function looksLikeProductTitle(string $text): bool
    {
        if (mb_strlen($text) < 12) {
            return false;
        }

        $badWords = [
            'Trang chủ', 'Danh Mục', 'Xem tất cả', 'Giỏ hàng', 'Tra cứu đơn hàng', 'Deal', 'Bán chạy nhất',
            'Hỗ trợ', 'Liên hệ', 'Tin tức', 'Video', 'Về Vinh Nguyễn', 'Chính sách', 'Download', 'Hướng dẫn',
        ];

        foreach ($badWords as $bad) {
            if (Str::contains($text, $bad)) {
                return false;
            }
        }

        return preg_match('/\d/u', $text) === 1 || preg_match('/[A-Z]{2,}/u', $text) === 1;
    }

    private function extractPriceNearLink(string $html, string $href, string $title): float
    {
        $quotedHref = preg_quote($href, '~');
        $quotedTitle = preg_quote($title, '~');

        $patterns = [
            '~' . $quotedHref . '.{0,500}?(\d[\d\.\,]*)\s*đ~si',
            '~' . $quotedTitle . '.{0,500}?(\d[\d\.\,]*)\s*đ~si',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                return $this->normalizePrice($m[1]);
            }
        }

        return 0.0;
    }

    private function normalizePrice(string $price): float
    {
        $clean = preg_replace('/[^0-9]/', '', $price) ?? '';
        return $clean !== '' ? (float) $clean : 0.0;
    }

    private function makeProductKey(string $url, string $title): string
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $slug = $path !== '' ? basename($path) : Str::slug($title);
        return Str::limit($slug, 255, '');
    }
}
