<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Tự lọc các cột chưa tồn tại trên DB (tránh lỗi 500 khi migration chưa chạy đồng bộ).
     */
    private function filterExistingCustomerColumns(array $validated): array
    {
        $columns = [
            'name',
            'tax_id',
            'tax_address',
            'address',
            'invoice_recipient',
            'email',
            'phone',
            'company_status',
            'representative',
            'managed_by',
            'active_date',
            'business_type',
            'main_business',
        ];

        $out = [];
        foreach ($columns as $col) {
            if (!array_key_exists($col, $validated)) continue;
            try {
                if (Schema::hasColumn('customers', $col)) {
                    $out[$col] = $validated[$col];
                }
            } catch (\Throwable $e) {
                // If schema inspection fails, just skip this column.
            }
        }
        return $out;
    }

    public function taxLookup(string $taxCode)
    {
        $taxCode = preg_replace('/\s+/', '', trim((string) $taxCode));
        if ($taxCode === '' || strlen($taxCode) < 8) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã số thuế không hợp lệ.',
            ], 422);
        }

        $debugMode = (bool) config('app.debug');
        try {
            if (request()->boolean('debug')) {
                $debugMode = true;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $lookupWantsBranch = str_contains($taxCode, '-');

        $isBranchPayload = function (array $payload) use ($taxCode, $lookupWantsBranch) {
            $name = mb_strtolower((string) ($payload['name'] ?? ''));
            $tid = (string) ($payload['tax_id'] ?? '');
            $src = mb_strtolower((string) ($payload['source_url'] ?? ''));
            $tidDigits = preg_replace('/\D+/', '', $tid);
            $taxDigits = preg_replace('/\D+/', '', (string) $taxCode);

            if ($taxDigits !== '' && $tidDigits !== '' && $tidDigits !== $taxDigits) return true;

            if ($lookupWantsBranch) {
                return false;
            }

            // Some branch pages show base tax_id without the -001 suffix.
            // Use source_url as an additional strong signal.
            if ($src !== '') {
                if (preg_match('/\bchi\-nhanh\b/iu', $src)) return true;
                if (preg_match('/\bvan\-phong\-dai\-dien\b/iu', $src)) return true;
                if (preg_match('/\bdia\-diem\-kinh\-doanh\b/iu', $src)) return true;
                if ($taxDigits !== '' && preg_match('/\/' . preg_quote($taxDigits, '/') . '\-\d{3,4}\b/iu', $src)) return true;
                if ($taxDigits !== '' && preg_match('/\/' . preg_quote($taxDigits, '/') . '\-\d{3,4}\-/iu', $src)) return true;
            }

            if ($name !== '' && preg_match('/\bchi\s*nh[aá]nh\b/iu', $name)) return true;
            if ($name !== '' && preg_match('/\bv[aă]n\s*ph[oò]ng\s*đ[aạ]i\s*di[eệ]n\b/iu', $name)) return true;
            if ($name !== '' && preg_match('/\bđ[iị]a\s*đi[eể]m\s*kinh\s*doanh\b/iu', $name)) return true;
            if (preg_match('/^\s*\d{3,4}\s*-/u', (string) ($payload['name'] ?? ''))) return true;
            if ($taxDigits !== '' && str_starts_with($tid, $taxCode . '-')) return true;

            return false;
        };

        $normalizeBranch001AsMain = function (array $payload) use ($taxCode, $lookupWantsBranch) {
            if ($lookupWantsBranch) return $payload;

            $tid = (string) ($payload['tax_id'] ?? '');
            $src = mb_strtolower((string) ($payload['source_url'] ?? ''));
            $name = mb_strtolower((string) ($payload['name'] ?? ''));

            // If Masothue returns {mst}-001 but the page does not look like a branch/VPDD/DDKD page,
            // accept it as a proxy for the main company rather than failing the lookup.
            $expected = $taxCode . '-001';
            if ($tid !== $expected) return $payload;
            if ($src !== '' && (preg_match('/\bchi\-nhanh\b/iu', $src) || preg_match('/\bvan\-phong\-dai\-dien\b/iu', $src) || preg_match('/\bdia\-diem\-kinh\-doanh\b/iu', $src))) {
                return $payload;
            }
            if ($name !== '' && (preg_match('/\bchi\s*nh[aá]nh\b/iu', $name) || preg_match('/\bv[aă]n\s*ph[oò]ng\s*đ[aạ]i\s*di[eệ]n\b/iu', $name) || preg_match('/\bđ[iị]a\s*đi[eể]m\s*kinh\s*doanh\b/iu', $name))) {
                return $payload;
            }

            $payload['tax_id'] = $taxCode;
            $payload['__note__'] = 'normalized_from_-001';
            return $payload;
        };

        $cacheKey = 'masothue.lookup.v18.' . $taxCode;

        try {
            $row = DB::table('tax_lookup_caches')->where('tax_code', $taxCode)->first();
            if ($row && isset($row->payload)) {
                $payload = is_string($row->payload) ? json_decode($row->payload, true) : (array) $row->payload;
                if (is_array($payload) && !empty($payload)) {
                    if ($isBranchPayload($payload)) {
                        DB::table('tax_lookup_caches')->where('tax_code', $taxCode)->delete();
                    } else {
                        $payload = $this->enrichTaxPayloadFromTracuuIfGaps($taxCode, $payload, $debugMode, $cacheKey, $isBranchPayload);
                        try {
                            DB::table('tax_lookup_caches')->updateOrInsert(
                                ['tax_code' => $taxCode],
                                [
                                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                                    'source_url' => (string) ($payload['source_url'] ?? ''),
                                    'fetched_at' => now(),
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                        } catch (\Throwable $e) {
                            // ignore
                        }

                        return response()->json([
                            'ok' => true,
                            'data' => $payload,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore if table doesn't exist yet or JSON invalid
        }

        $hasCache = Cache::has($cacheKey);
        $cached = $hasCache ? Cache::get($cacheKey) : null;

        if ($debugMode && $hasCache && $cached === '__NOT_FOUND__') {
            Cache::forget($cacheKey);
            $hasCache = false;
            $cached = null;
        }

        if ($hasCache && $cached !== '__NOT_FOUND__' && is_array($cached) && $isBranchPayload($cached)) {
            Cache::forget($cacheKey);
            $hasCache = false;
            $cached = null;
        }

        if ($hasCache) {
            if ($cached === '__NOT_FOUND__') {
                $viet = $this->lookupTaxViaVietQr($taxCode);
                if ($viet !== null && !$isBranchPayload($viet)) {
                    $data = $viet;
                    try {
                        Cache::put($cacheKey, $data, 60 * 60 * 24);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                } else {
                    $data = false;
                }
            } else {
                $data = $cached;
            }
        } else {
            $viet = $this->lookupTaxViaVietQr($taxCode);
            if ($viet !== null && !$isBranchPayload($viet)) {
                $data = $viet;
            } else {
                $data = (function () use ($taxCode, $debugMode, $lookupWantsBranch, $isBranchPayload, $normalizeBranch001AsMain) {
                $lock = null;
                try {
                    $lock = Cache::lock('masothue.lookup.lock.' . $taxCode, 20);
                    if ($lock) {
                        $lock->block(5);
                    }
                } catch (\Throwable $e) {
                    $lock = null;
                }

                try {

                $headers = [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                    'Upgrade-Insecure-Requests' => '1',
                    'Sec-CH-UA' => '"Chromium";v="122", "Google Chrome";v="122", "Not(A:Brand";v="99"',
                    'Sec-CH-UA-Mobile' => '?0',
                    'Sec-CH-UA-Platform' => '"Windows"',
                    'Sec-Fetch-Site' => 'none',
                    'Sec-Fetch-Mode' => 'navigate',
                    'Sec-Fetch-User' => '?1',
                    'Sec-Fetch-Dest' => 'document',
                    'Referer' => 'https://masothue.com/',
                ];

                $headers403 = $headers;
                $headers403['Referer'] = 'https://masothue.com/';
                $headers403['Sec-Fetch-Site'] = 'none';

                $attempts = [];
                $dbg = function (string $reason) use (&$attempts, $debugMode) {
                    if (!$debugMode) return null;
                    return [
                        '__DEBUG__' => true,
                        'attempts' => $attempts,
                        'reason' => $reason,
                    ];
                };

                $err = function (string $code, string $message, int $status = 429) use (&$attempts, $debugMode) {
                    $payload = [
                        '__ERROR__' => true,
                        'code' => $code,
                        'message' => $message,
                        'status' => $status,
                    ];
                    if ($debugMode) {
                        $payload['debug'] = [
                            '__DEBUG__' => true,
                            'attempts' => $attempts,
                            'reason' => $code,
                        ];
                    }
                    return $payload;
                };

                $circuitKey = 'masothue.circuit_open_until';
                try {
                    $openUntil = (int) Cache::get($circuitKey, 0);
                    if ($openUntil > time()) {
                        $wait = max(1, $openUntil - time());
                        return $err('circuit_open', 'Masothue đang chặn tạm thời, vui lòng thử lại sau ' . $wait . ' giây.', 429);
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

            $decode = function ($v) {
                $v = html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $v = preg_replace('/\s+/u', ' ', trim($v));
                return $v;
            };

            $parseDetailHtml = function (string $html, string $sourceUrl) use ($taxCode, $decode) {
                $normalizeText = function (string $v) use ($decode) {
                    return $decode($v);
                };

                // Avoid bare "cloudflare" / "captcha": real Masothue HTML includes cdn-cgi/cloudflare-static
                // and recaptcha placeholder divs, which falsely trip a block on every detail page.
                $blockedSignals = [
                    'checking your browser before accessing',
                    'cf-browser-verification',
                    'cf-challenge-running',
                    'why have i been blocked',
                    'attention required',
                    'access denied',
                    'ray id',
                    'please enable cookies',
                    'unusual traffic',
                ];
                $lowerHtml = mb_strtolower($html);
                foreach ($blockedSignals as $sig) {
                    if ($sig !== '' && str_contains($lowerHtml, $sig)) {
                        return false;
                    }
                }

                $normalizeLabel = function (string $v) use ($normalizeText) {
                    $v = $normalizeText($v);
                    $v = preg_replace('/\s+/u', ' ', trim((string) $v));
                    $v = preg_replace('/\s*:\s*$/u', '', (string) $v);
                    return trim((string) $v);
                };

                $domMap = [];
                $xpath = null;
                try {
                    $dom = new \DOMDocument();
                    $prev = libxml_use_internal_errors(true);
                    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
                    libxml_clear_errors();
                    libxml_use_internal_errors($prev);

                    $xpath = new \DOMXPath($dom);
                    $rows = $xpath->query('//tr[td or th]');
                    if ($rows) {
                        foreach ($rows as $tr) {
                            $cells = [];
                            foreach ($tr->childNodes as $child) {
                                if (!($child instanceof \DOMElement)) continue;
                                $tag = strtolower($child->tagName);
                                if ($tag === 'td' || $tag === 'th') {
                                    $cells[] = $normalizeText($child->textContent ?? '');
                                }
                            }

                            if (count($cells) >= 2) {
                                $label = $normalizeLabel((string) ($cells[0] ?? ''));
                                $value = $normalizeText((string) ($cells[1] ?? ''));
                                if ($label !== '') {
                                    $domMap[$label] = $value;
                                }
                            }
                        }
                    }

                    $copyNodes = $xpath->query('//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                    if ($copyNodes) {
                        foreach ($copyNodes as $copy) {
                            if (!($copy instanceof \DOMElement)) continue;

                            $value = $normalizeText((string) ($copy->textContent ?? ''));
                            if ($value === '') continue;

                            $label = '';
                            $container = $copy->parentNode;
                            $steps = 0;
                            while ($container && $steps < 4) {
                                if ($container instanceof \DOMElement) {
                                    $prevEl = $container->previousSibling;
                                    while ($prevEl && !($prevEl instanceof \DOMElement)) {
                                        $prevEl = $prevEl->previousSibling;
                                    }

                                    if ($prevEl instanceof \DOMElement) {
                                        $labelText = $normalizeLabel((string) ($prevEl->textContent ?? ''));
                                        if ($labelText !== '' && mb_strlen($labelText) <= 60) {
                                            $label = $labelText;
                                            break;
                                        }
                                    }
                                }

                                $container = $container ? $container->parentNode : null;
                                $steps++;
                            }

                            if ($label !== '' && !isset($domMap[$label])) {
                                $domMap[$label] = $value;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $domMap = [];
                    $xpath = null;
                }
 $pickFromDom = function (string $label) use ($xpath, $decode, $normalizeLabel, $normalizeText) {
                    if (!$xpath) return '';

                    try {
                        $nodes = $xpath->query('//*[contains(., ' . json_encode($label, JSON_UNESCAPED_UNICODE) . ')]');
                        if (!$nodes) return '';

                        foreach ($nodes as $n) {
                            if (!($n instanceof \DOMElement)) continue;
                            $t = $normalizeLabel((string) ($n->textContent ?? ''));
                            if ($t === '' || mb_stripos($t, $label) === false) continue;

                            $sib = $n->nextSibling;
                            while ($sib && !($sib instanceof \DOMElement)) {
                                $sib = $sib->nextSibling;
                            }

                            if ($sib instanceof \DOMElement) {
                                $v = $decode((string) ($sib->textContent ?? ''));
                                if ($v !== '') return $v;
                            }

                            $followingCopy = $xpath->query('following::span[contains(concat(" ", normalize-space(@class), " "), " copy ")][1]', $n);
                            if ($followingCopy && $followingCopy->length > 0) {
                                $el = $followingCopy->item(0);
                                if ($el instanceof \DOMElement) {
                                    $v = $decode((string) ($el->textContent ?? ''));
                                    if ($v !== '') return $v;
                                }
                            }

                            $container = $n->parentNode;
                            $depth = 0;
                            while ($container && $depth < 6) {
                                if ($container instanceof \DOMElement) {
                                    $copy = $xpath->query('.//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]', $container);
                                    if ($copy) {
                                        foreach ($copy as $c) {
                                            if (!($c instanceof \DOMElement)) continue;
                                            $v = $decode((string) ($c->textContent ?? ''));
                                            if ($v !== '') return $v;
                                        }
                                    }
                                }

                                $container = $container ? $container->parentNode : null;
                                $depth++;
                            }

                            $parent = $n->parentNode;
                            if ($parent instanceof \DOMElement) {
                                $els = [];
                                foreach ($parent->childNodes as $c) {
                                    if ($c instanceof \DOMElement) $els[] = $c;
                                }

                                foreach ($els as $i => $el) {
                                    if ($el->isSameNode($n)) {
                                        $next = $els[$i + 1] ?? null;
                                        if ($next instanceof \DOMElement) {
                                            $v = $decode((string) ($next->textContent ?? ''));
                                            if ($v !== '') return $v;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        return '';
                    }

                    return '';
                };

                $pickCell = function (string $label) use ($html, $decode, $domMap, $pickFromDom) {
                    foreach ($domMap as $k => $v) {
                        if (mb_stripos($k, $label) !== false) {
                            return $v;
                        }
                    }

                    $domVal = $pickFromDom($label);
                    if ($domVal !== '') {
                        return $domVal;
                    }

                    $labelQ = preg_quote($label, '/');
                    if (preg_match('/' . $labelQ . '[^<]{0,80}<\/[^>]+>\s*<span[^>]*class=["\"][^"\"]*\bcopy\b[^"\"]*["\"][^>]*>(.*?)<\/span>/siu', $html, $m)) {
                        return $decode(strip_tags((string) ($m[1] ?? '')));
                    }

                    $patterns = [
                        '/<td[^>]*>\s*' . $labelQ . '\s*<\/td>\s*<td[^>]*>(.*?)<\/td>/si',
                        '/<th[^>]*>\s*' . $labelQ . '\s*<\/th>\s*<td[^>]*>(.*?)<\/td>/si',
                        '/<span[^>]*>\s*' . $labelQ . '\s*<\/span>\s*<span[^>]*>(.*?)<\/span>/si',
                    ];

                    foreach ($patterns as $p) {
                        if (preg_match($p, $html, $m)) {
                            $val = strip_tags($m[1]);
                            return $decode($val);
                        }
                    }

                    return '';
                };

                $pickTitle = function () use ($html, $decode, $xpath) {
                    if ($xpath) {
                        try {
                            $n1 = $xpath->query('//*[@itemprop="name"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                            if ($n1 && $n1->length > 0) {
                                $el = $n1->item(0);
                                if ($el instanceof \DOMElement) {
                                    $v = (string) $el->getAttribute('data-original-title');
                                    $v = $decode($v !== '' ? $v : (string) ($el->textContent ?? ''));
                                    if ($v !== '') return $v;
                                }
                            }

                            $n2 = $xpath->query('//*[@itemprop="name"]');
                            if ($n2 && $n2->length > 0) {
                                $el = $n2->item(0);
                                if ($el instanceof \DOMElement) {
                                    $v = $decode((string) ($el->textContent ?? ''));
                                    if ($v !== '') return $v;
                                }
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }
                    }

                    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $html, $m)) {
                        return $decode(strip_tags($m[1]));
                    }
                    return '';
                };

                $name = $pickTitle();
                $name = preg_replace('/^\s*[0-9\-]+\s*\-\s*/u', '', (string) $name);
                $taxId = $pickCell('Mã số thuế');

                if ($name === '') {
                    try {
                        if (preg_match('/id=["\']company-name["\'][^>]*>.*?<span[^>]*class=["\'][^"\']*\bcopy\b[^"\']*["\'][^>]*>(.*?)<\/span>/siu', $html, $mName)) {
                            $name = $decode(strip_tags((string) ($mName[1] ?? '')));
                            $name = preg_replace('/^\s*[0-9\-]+\s*\-\s*/u', '', (string) $name);
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                // Stable Masothue anchors
                if ($taxId === '' && $xpath) {
                    try {
                        $nTax = $xpath->query('//*[@itemprop="taxID"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                        if ($nTax && $nTax->length > 0) {
                            $el = $nTax->item(0);
                            if ($el instanceof \DOMElement) {
                                $taxId = $decode((string) ($el->textContent ?? ''));
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($taxId === '') {
                    try {
                        if (preg_match('/itemprop=["\']taxID["\'][^>]*>.*?<span[^>]*class=["\'][^"\']*\bcopy\b[^"\']*["\'][^>]*>(.*?)<\/span>/siu', $html, $mTax)) {
                            $taxId = $decode(strip_tags((string) ($mTax[1] ?? '')));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $representative = $pickCell('Người đại diện');
                if ($representative === '') {
                    $repLabels = [
                        'Đại diện pháp luật',
                        'Đại diện theo pháp luật',
                        'Người đại diện theo pháp luật',
                        'Người đại diện pháp luật',
                    ];
                    foreach ($repLabels as $lb) {
                        $representative = $pickCell($lb);
                        if ($representative !== '') break;
                    }
                }
                if ($representative === '' && $xpath) {
                    try {
                        $nRep = $xpath->query('//*[@id="representative-name"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                        if ($nRep && $nRep->length > 0) {
                            $el = $nRep->item(0);
                            if ($el instanceof \DOMElement) {
                                $representative = $decode((string) ($el->textContent ?? ''));
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                // Masothue: <tr itemprop="alumni" itemscope itemtype="...Person"><td>… Người đại diện</td><td><span itemprop="name"><a>…
                if ($representative === '' && $xpath) {
                    try {
                        $nAlumni = $xpath->query('//tr[@itemprop="alumni"]//span[@itemprop="name"]//a');
                        if ($nAlumni && $nAlumni->length > 0) {
                            $el = $nAlumni->item(0);
                            if ($el instanceof \DOMElement) {
                                $representative = $decode((string) ($el->textContent ?? ''));
                            }
                        }
                        if ($representative === '') {
                            $nAlumni2 = $xpath->query('//tr[@itemprop="alumni"]//span[@itemprop="name"]');
                            if ($nAlumni2 && $nAlumni2->length > 0) {
                                $el2 = $nAlumni2->item(0);
                                if ($el2 instanceof \DOMElement) {
                                    $representative = $decode((string) ($el2->textContent ?? ''));
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($representative === '') {
                    try {
                        if (preg_match('/id=["\']representative-name["\'][^>]*>(.*?)<\/(?:td|div|section)>/siu', $html, $mRep1)) {
                            $representative = $decode(strip_tags((string) ($mRep1[1] ?? '')));
                        } elseif (preg_match('/id=["\']representative-name["\'][^>]*>(.*?)(?:<\/tr>|<\/table>)/siu', $html, $mRep2)) {
                            $representative = $decode(strip_tags((string) ($mRep2[1] ?? '')));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                if ($representative !== '') {
                    $cutMarkers = [
                        'Ngoài ra',
                        'Ngoai ra',
                        'còn đại diện',
                        'con dai dien',
                    ];

                    foreach ($cutMarkers as $mk) {
                        $pos = mb_stripos($representative, $mk);
                        if ($pos !== false) {
                            $representative = trim((string) mb_substr($representative, 0, $pos));
                            break;
                        }
                    }

                    $representative = preg_replace('/\s*,\s*$/u', '', (string) $representative);
                }

                $phone = $pickCell('Điện thoại');
                if ($phone === '' && $xpath) {
                    try {
                        $telTd = $xpath->query('//td[@itemprop="telephone"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                        if ($telTd && $telTd->length > 0) {
                            $telEl = $telTd->item(0);
                            if ($telEl instanceof \DOMElement) {
                                $candTd = $decode((string) ($telEl->textContent ?? ''));
                                if ($candTd !== '' && preg_match('/\b(0\d{8,10})\b/u', $candTd, $mTd)) {
                                    $phone = (string) ($mTd[1] ?? '');
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($phone === '' && $xpath) {
                    try {
                        $phoneNodes = $xpath->query('//*[contains(., "Điện thoại")]');
                        if ($phoneNodes) {
                            foreach ($phoneNodes as $pn) {
                                if (!($pn instanceof \DOMElement)) continue;
                                $t = $decode((string) ($pn->textContent ?? ''));
                                if ($t === '' || mb_stripos($t, 'Điện thoại') === false) continue;

                                $followingCopy = $xpath->query('following::span[contains(concat(" ", normalize-space(@class), " "), " copy ")][1]', $pn);
                                if ($followingCopy && $followingCopy->length > 0) {
                                    $el = $followingCopy->item(0);
                                    if ($el instanceof \DOMElement) {
                                        $cand = $decode((string) ($el->textContent ?? ''));
                                        if ($cand !== '' && preg_match('/\b(0\d{8,10})\b/u', $cand, $mCand)) {
                                            $phone = (string) ($mCand[1] ?? '');
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
                if ($phone !== '' && preg_match('/\b(0\d{8,10})\b/u', $phone, $mPhone)) {
                    $phone = (string) ($mPhone[1] ?? $phone);
                } elseif ($phone !== '' && preg_match('/\b(ẩn|an)\b/iu', $phone)) {
                    $phone = '';
                }

                if ($phone === '') {
                    $textAll = $decode(strip_tags($html));
                    if (preg_match_all('/\b(0\d{8,10})\b/u', $textAll, $mm) && !empty($mm[1])) {
                        $taxCodeDigits = preg_replace('/\D+/', '', (string) $taxCode);
                        $taxIdDigits = preg_replace('/\D+/', '', (string) $taxId);
                        foreach ($mm[1] as $cand) {
                            $cand = (string) $cand;
                            if ($cand === '') continue;
                            if ($cand === $taxCodeDigits) continue;
                            if ($taxIdDigits !== '' && $cand === $taxIdDigits) continue;
                            $phone = $cand;
                            break;
                        }
                    }
                }

                $taxAddress = $pickCell('Địa chỉ Thuế');
                if ($taxAddress === '' && $xpath) {
                    try {
                        $nAddrSelf = $xpath->query('//*[@id="tax-address-html"]');
                        if ($nAddrSelf && $nAddrSelf->length > 0) {
                            $el = $nAddrSelf->item(0);
                            if ($el instanceof \DOMElement) {
                                $cand = $decode((string) ($el->textContent ?? ''));
                                if ($cand !== '') {
                                    $taxAddress = $cand;
                                }
                            }
                        }

                        $nAddr = $xpath->query('//*[@id="tax-address-html"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                        if ($nAddr && $nAddr->length > 0) {
                            $el = $nAddr->item(0);
                            if ($el instanceof \DOMElement) {
                                $taxAddress = $decode((string) ($el->textContent ?? ''));
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($taxAddress === '') {
                    try {
                        if (preg_match('/id=["\']tax-address-html["\'][^>]*>(.*?)<\/span>/siu', $html, $mAddr0)) {
                            $taxAddress = $decode(strip_tags((string) ($mAddr0[1] ?? '')));
                        }
                        if (preg_match('/id=["\']tax-address-html["\'][^>]*>(.*?)<\/(?:td|div|section)>/siu', $html, $mAddr1)) {
                            $taxAddress = $decode(strip_tags((string) ($mAddr1[1] ?? '')));
                        } elseif (preg_match('/id=["\']tax-address-html["\'][^>]*>(.*?)(?:<\/tr>|<\/table>)/siu', $html, $mAddr2)) {
                            $taxAddress = $decode(strip_tags((string) ($mAddr2[1] ?? '')));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $companyStatus = $pickCell('Tình trạng');
                if ($companyStatus === '' && $xpath) {
                    try {
                        $nStSelf = $xpath->query('//*[@id="tax-status-html"]');
                        if ($nStSelf && $nStSelf->length > 0) {
                            $el = $nStSelf->item(0);
                            if ($el instanceof \DOMElement) {
                                $cand = $decode((string) ($el->textContent ?? ''));
                                if ($cand !== '') {
                                    $companyStatus = $cand;
                                }
                            }
                        }

                        $nSt = $xpath->query('//*[@id="tax-status-html"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")]');
                        if ($nSt && $nSt->length > 0) {
                            $el = $nSt->item(0);
                            if ($el instanceof \DOMElement) {
                                $companyStatus = $decode((string) ($el->textContent ?? ''));
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if ($companyStatus === '') {
                    try {
                        if (preg_match('/id=["\']tax-status-html["\'][^>]*>(.*?)<\/span>/siu', $html, $mSt0)) {
                            $companyStatus = $decode(strip_tags((string) ($mSt0[1] ?? '')));
                        }
                        if (preg_match('/id=["\']tax-status-html["\'][^>]*>(.*?)<\/(?:td|div|section)>/siu', $html, $mSt1)) {
                            $companyStatus = $decode(strip_tags((string) ($mSt1[1] ?? '')));
                        } elseif (preg_match('/id=["\']tax-status-html["\'][^>]*>(.*?)(?:<\/tr>|<\/table>)/siu', $html, $mSt2)) {
                            $companyStatus = $decode(strip_tags((string) ($mSt2[1] ?? '')));
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $payload = [
                    'tax_id' => $taxId !== '' ? $taxId : $taxCode,
                    'name' => $name,
                    'tax_address' => $taxAddress,
                    'address' => $pickCell('Địa chỉ'),
                    'company_status' => $companyStatus,
                    'representative' => $representative,
                    'phone' => $phone,
                    'active_date' => $pickCell('Ngày hoạt động'),
                    'managed_by' => $pickCell('Quản lý bởi'),
                    'business_type' => $pickCell('Loại hình DN'),
                    'main_business' => $pickCell('Ngành nghề chính'),
                    'source_url' => $sourceUrl,
                ];

                $isEmpty = ($payload['name'] ?? '') === ''
                    && ($payload['tax_address'] ?? '') === ''
                    && ($payload['address'] ?? '') === ''
                    && ($payload['representative'] ?? '') === ''
                    && ($payload['phone'] ?? '') === ''
                    && ($payload['company_status'] ?? '') === '';

                return $isEmpty ? false : $payload;
            };

            $cookieJar = new \GuzzleHttp\Cookie\CookieJar();
            $client = Http::timeout(15)
                ->withHeaders($headers)
                ->withOptions([
                    'cookies' => $cookieJar,
                    'allow_redirects' => true,
                ]);

            try {
                $homeRes = $client->get('https://masothue.com/');
                $attempts[] = ['url' => 'https://masothue.com/', 'status' => $homeRes->status(), 'ok' => $homeRes->ok()];
            } catch (\Throwable $e) {
                // ignore
            }

            $lookupWantsBranch = str_contains($taxCode, '-');
            $taxCodeBase = $lookupWantsBranch ? (string) explode('-', $taxCode, 2)[0] : '';

            if ($lookupWantsBranch && $taxCodeBase !== '' && $taxCodeBase !== $taxCode) {
                $detailUrl = 'https://masothue.com/' . urlencode($taxCode);
                $res = $client->get($detailUrl);
                if ($res->ok()) {
                    $html = (string) $res->body();
                    if ($html !== '') {
                        $parsed = $parseDetailHtml($html, $detailUrl);
                        if ($parsed) {
                            return $parsed;
                        }
                    }
                }

                usleep(random_int(150000, 450000));
                $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCodeBase) . '&type=taxCode';
                $searchRes = $client->get($searchUrl);
                if (!$searchRes->ok()) {
                    $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCodeBase);
                    $searchRes = $client->get($searchUrl);
                }

                if ($searchRes->status() === 403) {
                    usleep(random_int(300000, 800000));
                    $searchRes = $client->withHeaders($headers403)->get($searchUrl);
                }

                if ($searchRes->status() === 403) {
                    try {
                        Cache::put($circuitKey, time() + 180, 180);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    return $err('branch_search_403', 'Masothue đang chặn tra cứu, vui lòng thử lại sau ít phút.', 429);
                }

                if (!$searchRes->ok()) {
                    return $dbg('branch_search_http');
                }

                $searchHtml = (string) $searchRes->body();
                if ($searchHtml === '') {
                    return $dbg('branch_search_empty_html');
                }

                $detailPath = '';
                try {
                    $dom = new \DOMDocument();
                    $prev = libxml_use_internal_errors(true);
                    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $searchHtml);
                    libxml_clear_errors();
                    libxml_use_internal_errors($prev);

                    $xpath = new \DOMXPath($dom);
                    $anchors = $xpath->query('//a[@href]');
                    if ($anchors) {
                        $taxDigits = preg_replace('/\D+/', '', (string) $taxCode);
                        foreach ($anchors as $a) {
                            if (!($a instanceof \DOMElement)) continue;
                            $href = (string) $a->getAttribute('href');
                            if ($href === '') continue;
                            if (stripos($href, $taxCode) === false) continue;

                            if (str_starts_with($href, 'http')) {
                                $u = parse_url($href);
                                $path = (string) ($u['path'] ?? '');
                                if ($path !== '') {
                                    $detailPath = $path;
                                    break;
                                }
                            } else {
                                $detailPath = $href;
                                break;
                            }
                        }

                        if ($detailPath === '') {
                            foreach ($anchors as $a) {
                                if (!($a instanceof \DOMElement)) continue;
                                $href = (string) $a->getAttribute('href');
                                if ($href === '') continue;
                                if ($taxCodeBase !== '' && stripos($href, $taxCodeBase) === false) continue;

                                $t = (string) ($a->textContent ?? '');
                                $pt = ($a->parentNode instanceof \DOMElement) ? (string) ($a->parentNode->textContent ?? '') : '';
                                $tt = $t . ' ' . $pt;
                                $ttDigits = preg_replace('/\D+/', '', $tt);
                                if ($taxDigits !== '' && $ttDigits !== '' && stripos($ttDigits, $taxDigits) === false) {
                                    continue;
                                }

                                if (str_starts_with($href, 'http')) {
                                    $u = parse_url($href);
                                    $path = (string) ($u['path'] ?? '');
                                    if ($path !== '') {
                                        $detailPath = $path;
                                        break;
                                    }
                                } else {
                                    $detailPath = $href;
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                if ($detailPath === '') {
                    $p2 = '/href=["\'](\/[^"\']*' . preg_quote($taxCode, '/') . '[^"\']*)["\']/i';
                    if (preg_match($p2, $searchHtml, $m2)) {
                        $detailPath = (string) ($m2[1] ?? '');
                    }
                }

                if ($detailPath === '') {
                    $taxCodeNoDash = str_replace('-', '', (string) $taxCode);
                    if ($taxCodeNoDash !== '' && $taxCodeNoDash !== $taxCode) {
                        $p3 = '/href=["\'](\/[^"\']*' . preg_quote($taxCodeNoDash, '/') . '[^"\']*)["\']/i';
                        if (preg_match($p3, $searchHtml, $m3)) {
                            $detailPath = (string) ($m3[1] ?? '');
                        }
                    }
                }

                if ($detailPath === '') {
                    $taxCodeNoDash = str_replace('-', '', (string) $taxCode);
                    $prefetchPatterns = [
                        '/data-prefetch=["\'](\/[^"\']*' . preg_quote($taxCode, '/') . '[^"\']*)["\']/i',
                    ];
                    if ($taxCodeNoDash !== '' && $taxCodeNoDash !== $taxCode) {
                        $prefetchPatterns[] = '/data-prefetch=["\'](\/[^"\']*' . preg_quote($taxCodeNoDash, '/') . '[^"\']*)["\']/i';
                    }

                    foreach ($prefetchPatterns as $pp) {
                        if (preg_match($pp, $searchHtml, $mp)) {
                            $detailPath = (string) ($mp[1] ?? '');
                            if ($detailPath !== '') break;
                        }
                    }
                }

                if ($detailPath !== '') {
                    $detailUrl = 'https://masothue.com' . $detailPath;
                    $res = $client->get($detailUrl);
                    $attempts[] = ['url' => $detailUrl, 'status' => $res->status(), 'ok' => $res->ok()];
                    if ($res->ok()) {
                        $html = (string) $res->body();
                        if ($html !== '') {
                            $parsed = $parseDetailHtml($html, $detailUrl);
                            if ($parsed) {
                                return $parsed;
                            }
                        }
                    }
                }
            }

            $detailUrl = 'https://masothue.com/' . urlencode($taxCode);
            $res = $client->get($detailUrl);
            $attempts[] = ['url' => $detailUrl, 'status' => $res->status(), 'ok' => $res->ok()];

            $parsedFromDetail = null;
            if ($res->ok()) {
                $html = (string) $res->body();
                if ($html !== '') {
                    $parsedFromDetail = $parseDetailHtml($html, $detailUrl);
                    if ($parsedFromDetail && !$lookupWantsBranch && $isBranchPayload($parsedFromDetail)) {
                        $parsedFromDetail = null;
                    }
                    if ($parsedFromDetail) {
                        return $parsedFromDetail;
                    }
                }
            }

            if (!$res->ok() || !$parsedFromDetail) {
                $lookupWantsBranch = str_contains($taxCode, '-');
                $taxCodeBase = $lookupWantsBranch ? (string) explode('-', $taxCode, 2)[0] : '';
                $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCode) . '&type=taxCode';
                $searchRes = $client->get($searchUrl);
                $attempts[] = ['url' => $searchUrl, 'status' => $searchRes->status(), 'ok' => $searchRes->ok()];

                if (!$searchRes->ok()) {
                    $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCode);
                    $searchRes = $client->get($searchUrl);
                    $attempts[] = ['url' => $searchUrl, 'status' => $searchRes->status(), 'ok' => $searchRes->ok()];
                }

                if ($searchRes->status() === 403) {
                    usleep(random_int(300000, 800000));
                    $searchRes = $client->withHeaders($headers403)->get($searchUrl);
                    $attempts[] = ['url' => $searchUrl, 'status' => $searchRes->status(), 'ok' => $searchRes->ok()];
                }

                if ($searchRes->status() === 403) {
                    try {
                        Cache::put($circuitKey, time() + 180, 180);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                    return $err('search_403', 'Masothue đang chặn tra cứu, vui lòng thử lại sau ít phút.', 429);
                }

                if (!$searchRes->ok()) {
                    return $dbg('search_http');
                }

                $searchHtml = (string) $searchRes->body();
                if ($searchHtml === '') {
                    return $dbg('search_empty_html');
                }

                $detailPath = '';
                try {
                    $dom = new \DOMDocument();
                    $prev = libxml_use_internal_errors(true);
                    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $searchHtml);
                    libxml_clear_errors();
                    libxml_use_internal_errors($prev);

                    $xpath = new \DOMXPath($dom);
                    $anchors = $xpath->query('//a[@href]');
                    if ($anchors) {
                        $candidates = [];
                        foreach ($anchors as $a) {
                            if (!($a instanceof \DOMElement)) continue;
                            $href = (string) $a->getAttribute('href');
                            if ($href === '') continue;

                            if (stripos($href, $taxCode) !== false) {
                                if (str_starts_with($href, 'http')) {
                                    $u = parse_url($href);
                                    $path = (string) ($u['path'] ?? '');
                                    if ($path !== '') {
                                        $candidates[] = $path;
                                    }
                                } else {
                                    $candidates[] = $href;
                                }
                            }
                        }

                        // Masothue often uses data-prefetch on list items; include those as candidates too.
                        try {
                            $prefetchNodes = $xpath->query('//*[@data-prefetch]');
                            if ($prefetchNodes) {
                                foreach ($prefetchNodes as $n) {
                                    if (!($n instanceof \DOMElement)) continue;
                                    $pref = (string) $n->getAttribute('data-prefetch');
                                    if ($pref === '') continue;
                                    if (stripos($pref, $taxCode) === false) continue;
                                    if (str_starts_with($pref, 'http')) {
                                        $u = parse_url($pref);
                                        $path = (string) ($u['path'] ?? '');
                                        if ($path !== '') {
                                            $candidates[] = $path;
                                        }
                                    } else {
                                        $candidates[] = $pref;
                                    }
                                }
                            }
                        } catch (\Throwable $e) {
                            // ignore
                        }

                        if (!empty($candidates)) {
                            $candidates = array_values(array_unique(array_map(function ($p) {
                                $p = (string) $p;
                                if ($p === '') return '';
                                if (!str_starts_with($p, '/')) {
                                    $p = '/' . ltrim($p, '/');
                                }
                                return $p;
                            }, $candidates)));
                            $candidates = array_values(array_filter($candidates));

                            // Hard preference: when looking up main-company MST (no hyphen), prefer slug where
                            // char right after "{mst}-" is NOT a digit => /{mst}-cong-ty-...
                            if (!$lookupWantsBranch) {
                                foreach ($candidates as $p0) {
                                    if (preg_match('/^\/' . preg_quote($taxCode, '/') . '-\d/u', $p0)) {
                                        continue;
                                    }
                                    if (preg_match('/^\/' . preg_quote($taxCode, '/') . '-/u', $p0)) {
                                        $detailPath = $p0;
                                        break;
                                    }
                                }
                            }

                            if ($detailPath !== '') {
                                // already selected by hard preference
                            } else {
                            $best = '';
                            $bestScore = -9999;
                            $bestNonBranch = '';
                            $bestNonBranchScore = -9999;
                            foreach ($candidates as $p) {
                                $p = (string) $p;
                                if ($p === '') continue;
                                if (!str_starts_with($p, '/')) {
                                    $p = '/' . ltrim($p, '/');
                                }

                                $score = 0;
                                $lp = mb_strtolower($p);
                                if ($lookupWantsBranch) {
                                    if (str_starts_with($lp, '/' . mb_strtolower($taxCode))) $score += 100;
                                    if (preg_match('/\bchi-nhanh\b/u', $lp)) $score += 10;
                                } else {
                                    // Strongly avoid branch/VPĐD/ĐĐKD pages when user entered a main-company tax code.
                                    if (preg_match('/\bchi-nhanh\b/u', $lp)) $score -= 200;
                                    if (preg_match('/\bvan-phong-dai-dien\b/u', $lp)) $score -= 200;
                                    if (preg_match('/\bdia-diem-kinh-doanh\b/u', $lp)) $score -= 200;
                                    if (preg_match('/\bcn\b/u', $lp)) $score -= 30;

                                    // Explicit branch number right after MST: /{mst}-001-...
                                    if (preg_match('/^\/' . preg_quote($taxCode, '/') . '-\d{3,4}-/u', $p)) $score -= 500;

                                    // Generic branch-like patterns
                                    if (preg_match('/\/' . preg_quote($taxCode, '/') . '-\d{3,4}\b/u', $p)) $score -= 200;
                                    if (preg_match('/\/' . preg_quote($taxCode, '/') . '-\d{3,4}-/u', $p)) $score -= 200;
                                }
                                if (preg_match('/\/' . preg_quote($taxCode, '/') . '(?:\b|\-)/u', $p)) $score += 10;
                                if ($lp === '/' . $taxCode) $score += 20;
                                if (str_starts_with($lp, '/' . $taxCode . '-')) {
                                    if ($lookupWantsBranch) {
                                        $score += 15;
                                    } else {
                                        // Boost main-company slug /{mst}-cong-ty-..., not /{mst}-001-...
                                        if (!preg_match('/^\/' . preg_quote($taxCode, '/') . '-\d{3,4}-/u', $p)) {
                                            $score += 30;
                                        }
                                    }
                                }

                                if (!$lookupWantsBranch) {
                                    $isBranchLike = false;
                                    if (preg_match('/^\/' . preg_quote($taxCode, '/') . '-\d{3,4}-/u', $p)) $isBranchLike = true;
                                    if (preg_match('/\bchi-nhanh\b/u', $lp)) $isBranchLike = true;
                                    if (preg_match('/\bvan-phong-dai-dien\b/u', $lp)) $isBranchLike = true;
                                    if (preg_match('/\bdia-diem-kinh-doanh\b/u', $lp)) $isBranchLike = true;
                                    if (!$isBranchLike && $score > $bestNonBranchScore) {
                                        $bestNonBranchScore = $score;
                                        $bestNonBranch = $p;
                                    }
                                }

                                if ($score > $bestScore) {
                                    $bestScore = $score;
                                    $best = $p;
                                }
                            }

                            if (!$lookupWantsBranch && $bestNonBranch !== '') {
                                $detailPath = $bestNonBranch;
                            } elseif ($best !== '') {
                                $detailPath = $best;
                            }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

                $patterns = [
                    '/href=["\'](\/' . preg_quote($taxCode, '/') . '[^"\']+)["\']/i',
                    '/href=["\'](\/' . preg_quote($taxCode, '/') . ')["\']/i',
                    '/href=["\'](\/[^"\']*' . preg_quote($taxCode, '/') . '[^"\']*)["\']/i',
                ];

                if ($detailPath === '') {
                    foreach ($patterns as $p) {
                        if (preg_match($p, $searchHtml, $m)) {
                            $detailPath = (string) ($m[1] ?? '');
                            if ($detailPath !== '') {
                                break;
                            }
                        }
                    }
                }

                if ($detailPath === '' && $lookupWantsBranch && $taxCodeBase !== '' && $taxCodeBase !== $taxCode) {
                    $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCodeBase) . '&type=taxCode';
                    $searchRes2 = $client->get($searchUrl);
                    if (!$searchRes2->ok()) {
                        $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCodeBase);
                        $searchRes2 = $client->get($searchUrl);
                    }

                    if ($searchRes2->ok()) {
                        $searchHtml2 = (string) $searchRes2->body();
                        if ($searchHtml2 !== '') {
                            $detailPath2 = '';
                            try {
                                $dom2 = new \DOMDocument();
                                $prev2 = libxml_use_internal_errors(true);
                                $dom2->loadHTML('<?xml encoding="utf-8" ?>' . $searchHtml2);
                                libxml_clear_errors();
                                libxml_use_internal_errors($prev2);

                                $xpath2 = new \DOMXPath($dom2);
                                $anchors2 = $xpath2->query('//a[@href]');
                                if ($anchors2) {
                                    foreach ($anchors2 as $a2) {
                                        if (!($a2 instanceof \DOMElement)) continue;
                                        $href2 = (string) $a2->getAttribute('href');
                                        if ($href2 === '') continue;
                                        if (stripos($href2, $taxCode) === false) continue;

                                        if (str_starts_with($href2, 'http')) {
                                            $u2 = parse_url($href2);
                                            $path2 = (string) ($u2['path'] ?? '');
                                            if ($path2 !== '') {
                                                $detailPath2 = $path2;
                                                break;
                                            }
                                        } else {
                                            $detailPath2 = $href2;
                                            break;
                                        }
                                    }
                                }
                            } catch (\Throwable $e) {
                                // ignore
                            }

                            if ($detailPath2 === '') {
                                $p2 = '/href=["\'](\/[^"\']*' . preg_quote($taxCode, '/') . '[^"\']*)["\']/i';
                                if (preg_match($p2, $searchHtml2, $m2)) {
                                    $detailPath2 = (string) ($m2[1] ?? '');
                                }
                            }

                            if ($detailPath2 !== '') {
                                $detailPath = $detailPath2;
                            }
                        }
                    }
                }

                if ($detailPath === '') {
                    if (preg_match('/kh\s*ông\s*(tìm\s*thấy|có\s*kết\s*quả)/iu', $searchHtml)) {
                        return false;
                    }
                    return $dbg('search_no_detail_path');
                }

                $detailUrl = 'https://masothue.com' . $detailPath;
                $res = $client->get($detailUrl);
                $attempts[] = ['url' => $detailUrl, 'status' => $res->status(), 'ok' => $res->ok()];
                if (!$res->ok()) {
                    return $dbg('detail_http_after_search');
                }
            }

            $html = (string) $res->body();
            if ($html === '') {
                return $dbg('detail_empty_html');
            }

            $detailHtmlPreview = null;
            $detailMarkers = null;
            $detailSnippets = null;
            if ($debugMode) {
                try {
                    $detailHtmlPreview = mb_substr($decode($html), 0, 420);
                } catch (\Throwable $e) {
                    $detailHtmlPreview = null;
                }

                try {
                    $lower = mb_strtolower((string) $html);
                    $detailMarkers = [
                        'has_tax_address_html' => str_contains($lower, 'tax-address-html'),
                        'has_tax_status_html' => str_contains($lower, 'tax-status-html'),
                        'has_representative_name' => str_contains($lower, 'representative-name'),
                        'has_itemprop_taxid' => str_contains($lower, 'itemprop="taxid"') || str_contains($lower, 'itemprop=\'taxid\''),
                        'has_company_name' => str_contains($lower, 'company-name'),
                        'has_table_taxinfo' => str_contains($lower, 'table-taxinfo'),
                    ];
                } catch (\Throwable $e) {
                    $detailMarkers = null;
                }

                try {
                    $detailSnippets = [
                        'tax_id' => null,
                        'tax_address' => null,
                        'tax_status' => null,
                    ];
                    if (preg_match('/(.{0,160}itemprop=["\']taxID["\'].{0,420})/siu', $html, $m1)) {
                        $detailSnippets['tax_id'] = mb_substr($decode((string) ($m1[1] ?? '')), 0, 520);
                    }
                    if (preg_match('/(.{0,160}tax-address-html.{0,520})/siu', $html, $m2)) {
                        $detailSnippets['tax_address'] = mb_substr($decode((string) ($m2[1] ?? '')), 0, 520);
                    }
                    if (preg_match('/(.{0,160}tax-status-html.{0,520})/siu', $html, $m3)) {
                        $detailSnippets['tax_status'] = mb_substr($decode((string) ($m3[1] ?? '')), 0, 520);
                    }
                } catch (\Throwable $e) {
                    $detailSnippets = null;
                }
            }

            $parsed = $parseDetailHtml($html, $detailUrl);
            if ($parsed) {
                $parsed = $normalizeBranch001AsMain($parsed);
                if (!$lookupWantsBranch && $isBranchPayload($parsed)) {
                    $parsed = null;
                }
            }

            // Last-resort: if DOM parsing fails but HTML is clearly a Masothue detail page, extract via regex.
            if (!$parsed && !$lookupWantsBranch) {
                try {
                    $rxDecode = function ($v) use ($decode) {
                        return $decode(strip_tags((string) $v));
                    };

                    $rxTaxId = '';
                    if (preg_match('/itemprop\s*=\s*["\']taxID["\'][^>]*>\s*<span[^>]*class=["\'][^"\']*\bcopy\b[^"\']*["\'][^>]*>(.*?)<\/span>/siu', $html, $mTid)) {
                        $rxTaxId = $rxDecode($mTid[1] ?? '');
                    }

                    $rxTaxAddress = '';
                    if (preg_match('/id\s*=\s*["\']tax-address-html["\'][^>]*>(.*?)<\/span>/siu', $html, $mAddr)) {
                        $rxTaxAddress = $rxDecode($mAddr[1] ?? '');
                    }

                    $rxStatus = '';
                    if (preg_match('/id\s*=\s*["\']tax-status-html["\'][^>]*>(.*?)<\/td>/siu', $html, $mSt)) {
                        $rxStatus = $rxDecode($mSt[1] ?? '');
                    }

                    $rxRep = '';
                    if (preg_match('/Ng\s*\W*\s*\u0111\u1ea1i\s*di\u1ec7n\s*<\/td>\s*<td[^>]*>.*?itemprop\s*=\s*["\']name["\'][^>]*>\s*<a[^>]*>(.*?)<\/a>/siu', $html, $mRep)) {
                        $rxRep = $rxDecode($mRep[1] ?? '');
                    }

                    $rxName = '';
                    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/siu', $html, $mName)) {
                        $rxName = $rxDecode($mName[1] ?? '');
                        $rxName = preg_replace('/^\s*[0-9\-]+\s*\-\s*/u', '', (string) $rxName);
                    }

                    $rxPayload = [
                        'tax_id' => $rxTaxId !== '' ? $rxTaxId : $taxCode,
                        'name' => $rxName,
                        'tax_address' => $rxTaxAddress,
                        'address' => '',
                        'company_status' => $rxStatus,
                        'representative' => $rxRep,
                        'phone' => '',
                        'active_date' => '',
                        'managed_by' => '',
                        'business_type' => '',
                        'main_business' => '',
                        'source_url' => $detailUrl,
                    ];

                    $rxPayload = $normalizeBranch001AsMain($rxPayload);
                    $hasAny = ($rxPayload['tax_address'] ?? '') !== '' || ($rxPayload['company_status'] ?? '') !== '' || ($rxPayload['representative'] ?? '') !== '' || ($rxPayload['name'] ?? '') !== '';
                    if ($hasAny && !$isBranchPayload($rxPayload)) {
                        $parsed = $rxPayload;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            
            if (!$parsed && !$lookupWantsBranch) {
                $parentPath = '';
                try {
                    $rx = '/href=["\'](\/' . preg_quote($taxCode, '/') . '\-(?!\d{3,4}\-)[^"\']+)["\']/iu';
                    if (preg_match($rx, $html, $mm)) {
                        $parentPath = (string) ($mm[1] ?? '');
                    }
                } catch (\Throwable $e) {
                    $parentPath = '';
                }

                if ($parentPath !== '') {
                    $parentUrl = 'https://masothue.com' . $parentPath;
                    try {
                        $resP = $client->get($parentUrl);
                        $attempts[] = ['url' => $parentUrl, 'status' => $resP->status(), 'ok' => $resP->ok(), 'note' => 'parent_from_branch'];
                        if ($resP->ok()) {
                            $htmlP = (string) $resP->body();
                            if ($htmlP !== '') {
                                if ($debugMode) {
                                    try {
                                        $attempts[] = ['url' => $parentUrl, 'status' => $resP->status(), 'ok' => $resP->ok(), 'note' => 'parent_html_preview', 'body_preview' => mb_substr($decode($htmlP), 0, 420)];
                                    } catch (\Throwable $e) {
                                        // ignore
                                    }
                                }
                                $parsedP = $parseDetailHtml($htmlP, $parentUrl);
                                if ($parsedP) {
                                    $parsedP = $normalizeBranch001AsMain($parsedP);
                                    if (!$isBranchPayload($parsedP)) {
                                        return $parsedP;
                                    }
                                }

                                // Last-resort for parent page too (in case DOM parsing fails)
                                if (!$parsedP) {
                                    try {
                                        $rxDecode = function ($v) use ($decode) {
                                            return $decode(strip_tags((string) $v));
                                        };

                                        $rxTaxId = '';
                                        if (preg_match('/itemprop\s*=\s*["\']taxID["\'][^>]*>\s*<span[^>]*class=["\'][^"\']*\bcopy\b[^"\']*["\'][^>]*>(.*?)<\/span>/siu', $htmlP, $mTid)) {
                                            $rxTaxId = $rxDecode($mTid[1] ?? '');
                                        }

                                        $rxTaxAddress = '';
                                        if (preg_match('/id\s*=\s*["\']tax-address-html["\'][^>]*>(.*?)<\/span>/siu', $htmlP, $mAddr)) {
                                            $rxTaxAddress = $rxDecode($mAddr[1] ?? '');
                                        }

                                        $rxStatus = '';
                                        if (preg_match('/id\s*=\s*["\']tax-status-html["\'][^>]*>(.*?)<\/td>/siu', $htmlP, $mSt)) {
                                            $rxStatus = $rxDecode($mSt[1] ?? '');
                                        }

                                        $rxName = '';
                                        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/siu', $htmlP, $mName)) {
                                            $rxName = $rxDecode($mName[1] ?? '');
                                            $rxName = preg_replace('/^\s*[0-9\-]+\s*\-\s*/u', '', (string) $rxName);
                                        }

                                        $rxPayload = [
                                            'tax_id' => $rxTaxId !== '' ? $rxTaxId : $taxCode,
                                            'name' => $rxName,
                                            'tax_address' => $rxTaxAddress,
                                            'address' => '',
                                            'company_status' => $rxStatus,
                                            'representative' => '',
                                            'phone' => '',
                                            'active_date' => '',
                                            'managed_by' => '',
                                            'business_type' => '',
                                            'main_business' => '',
                                            'source_url' => $parentUrl,
                                        ];

                                        $rxPayload = $normalizeBranch001AsMain($rxPayload);
                                        $hasAny = ($rxPayload['tax_address'] ?? '') !== '' || ($rxPayload['company_status'] ?? '') !== '' || ($rxPayload['name'] ?? '') !== '';
                                        if ($hasAny && !$isBranchPayload($rxPayload)) {
                                            return $rxPayload;
                                        }
                                    } catch (\Throwable $e) {
                                        // ignore
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        $attempts[] = ['url' => $parentUrl, 'status' => null, 'ok' => false, 'note' => 'parent_from_branch_exception'];
                    }
                }
            }

            if (!$parsed && $debugMode) {
                return [
                    '__DEBUG__' => true,
                    'attempts' => $attempts,
                    'reason' => 'detail_parse_failed',
                    'detail_body_preview' => $detailHtmlPreview,
                    'detail_markers' => $detailMarkers,
                    'detail_snippets' => $detailSnippets,
                ];
            }
            return $parsed;
                } finally {
                    try {
                        if ($lock) {
                            $lock->release();
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            })();

            }

            if (is_array($data) && (($data['__ERROR__'] ?? false) === true)) {
                Cache::forget($cacheKey);
            } elseif ($data === false) {
                if ($debugMode) {
                    Cache::forget($cacheKey);
                } else {
                    $notFoundTtl = $lookupWantsBranch ? (60 * 5) : (60 * 60);
                    Cache::put($cacheKey, '__NOT_FOUND__', $notFoundTtl);
                }
            } elseif ($data) {
                if (is_array($data) && (($data['__DEBUG__'] ?? false) === true)) {
                    Cache::forget($cacheKey);
                } elseif ($isBranchPayload($data)) {
                    Cache::put($cacheKey, '__NOT_FOUND__', 60 * 10);
                } else {
                    Cache::put($cacheKey, $data, 60 * 60 * 24);
                }
            }
        }

        if (is_array($data) && (($data['__ERROR__'] ?? false) === true)) {
            $masothueBlockedCodes = ['circuit_open', 'search_403', 'branch_search_403'];
            if (in_array((string) ($data['code'] ?? ''), $masothueBlockedCodes, true)) {
                $fbDebug = null;
                try {
                    $fb = $this->fallbackTaxLookupTracuuMasothue($taxCode, $debugMode, $fbDebug);
                } catch (\Throwable $e) {
                    $fb = false;
                }
                if (is_array($fb) && !empty($fb) && !$isBranchPayload($fb)) {
                    $data = $fb;
                    try {
                        Cache::put($cacheKey, $data, 60 * 60 * 24);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        }

        if (is_array($data) && (($data['__ERROR__'] ?? false) === true)) {
            $status = (int) ($data['status'] ?? 429);
            $payload = [
                'ok' => false,
                'message' => (string) ($data['message'] ?? 'Không tra cứu được mã số thuế.'),
            ];
            if ($debugMode && isset($data['debug'])) {
                $payload['debug'] = $data['debug'];
            }
            return response()->json($payload, $status);
        }

        if ($data === false) {
            if ($debugMode) {
                $debugPayload = [
                    '__DEBUG__' => true,
                    'reason' => 'not_found',
                ];

                return response()->json([
                    'ok' => false,
                    'message' => 'Không tìm thấy mã số thuế (debug).',
                    'debug' => $debugPayload,
                ], 404);
            }

            return response()->json([
                'ok' => false,
                'message' => 'Không tìm thấy mã số thuế.',
            ], 404);
        }

        if (is_array($data) && (($data['__DEBUG__'] ?? false) === true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Không tra cứu được mã số thuế (debug).',
                'debug' => $data,
            ], 502);
        }

        if (!$data) {
            return response()->json([
                'ok' => false,
                'message' => 'Không tra cứu được mã số thuế.',
            ], 502);
        }

        $data = $this->enrichTaxPayloadFromTracuuIfGaps($taxCode, $data, $debugMode, $cacheKey, $isBranchPayload);

        if (!$isBranchPayload($data)) {
            try {
                DB::table('tax_lookup_caches')->updateOrInsert(
                    ['tax_code' => $taxCode],
                    [
                        'payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
                        'source_url' => (string) ($data['source_url'] ?? ''),
                        'fetched_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return response()->json([
            'ok' => true,
            'data' => $data,
        ]);
    }

    /**
     * Fill empty fields on $base from $fill (e.g. VietQR + tracuu-masothue). Never overwrites non-empty values.
     */
    private function mergeTaxLookupPayloads(array $base, array $fill): array
    {
        $keys = [
            'representative',
            'phone',
            'company_status',
            'managed_by',
            'business_type',
            'main_business',
            'active_date',
            'tax_address',
            'address',
            'name',
        ];
        foreach ($keys as $k) {
            $b = trim((string) ($base[$k] ?? ''));
            $f = trim((string) ($fill[$k] ?? ''));
            if ($b === '' && $f !== '') {
                $base[$k] = $f;
            }
        }
        $bTid = trim((string) ($base['tax_id'] ?? ''));
        $fTid = trim((string) ($fill['tax_id'] ?? ''));
        if ($bTid === '' && $fTid !== '') {
            $base['tax_id'] = $fTid;
        }

        return $base;
    }

    /**
     * Fill gaps (e.g. representative) via tracuu-masothue when VietQR or DB cache rows lack them.
     *
     * @param  callable(array): bool  $isBranchPayload
     */
    private function enrichTaxPayloadFromTracuuIfGaps(
        string $taxCode,
        array $data,
        bool $debugMode,
        string $cacheKey,
        callable $isBranchPayload
    ): array {
        if ($isBranchPayload($data)) {
            return $data;
        }

        $enrichGapFields = [
            'representative',
            'phone',
            'company_status',
            'business_type',
            'managed_by',
        ];
        $needsEnrich = false;
        foreach ($enrichGapFields as $gf) {
            if (trim((string) ($data[$gf] ?? '')) === '') {
                $needsEnrich = true;
                break;
            }
        }
        if (!$needsEnrich) {
            return $data;
        }

        $changed = false;

        // 1) First try tracuu-masothue API (cheaper than full Masothue parsing).
        $fbDebug = null;
        try {
            $enrich = $this->fallbackTaxLookupTracuuMasothue($taxCode, $debugMode, $fbDebug);
        } catch (\Throwable $e) {
            $enrich = false;
        }
        if (is_array($enrich) && !empty($enrich) && !$isBranchPayload($enrich)) {
            $beforeRep = trim((string) ($data['representative'] ?? ''));
            $beforeName = trim((string) ($data['name'] ?? ''));

            $data = $this->mergeTaxLookupPayloads($data, $enrich);

            // Avoid false positives: only mark changed if representative (or other key legal fields) became non-empty.
            $afterRep = trim((string) ($data['representative'] ?? ''));
            $afterName = trim((string) ($data['name'] ?? ''));
            if ($afterRep !== $beforeRep || $afterName !== $beforeName) {
                $changed = true;
            }
        }

        // 2) If tracuu still didn't provide "Người đại diện", try Masothue exactly once for representative.
        $repNow = trim((string) ($data['representative'] ?? ''));
        $phoneNow = trim((string) ($data['phone'] ?? ''));
        $statusNow = trim((string) ($data['company_status'] ?? ''));
        $bizTypeNow = trim((string) ($data['business_type'] ?? ''));

        $needsMasothueFields = ($repNow === '')
            || ($phoneNow === '')
            || ($statusNow === '')
            || ($bizTypeNow === '');

        if ($needsMasothueFields) {
            try {
                $fields = $this->lookupCompanyFieldsFromMasothue($taxCode, $debugMode);
                if (is_array($fields) && !empty($fields) && !$isBranchPayload($fields)) {
                    $beforeRep = trim((string) ($data['representative'] ?? ''));
                    $beforePhone = trim((string) ($data['phone'] ?? ''));
                    $beforeStatus = trim((string) ($data['company_status'] ?? ''));
                    $beforeBiz = trim((string) ($data['business_type'] ?? ''));

                    $data = $this->mergeTaxLookupPayloads($data, $fields);

                    $afterRep = trim((string) ($data['representative'] ?? ''));
                    $afterPhone = trim((string) ($data['phone'] ?? ''));
                    $afterStatus = trim((string) ($data['company_status'] ?? ''));
                    $afterBiz = trim((string) ($data['business_type'] ?? ''));

                    if ($afterRep !== $beforeRep || $afterPhone !== $beforePhone || $afterStatus !== $beforeStatus || $afterBiz !== $beforeBiz) {
                        $changed = true;
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if ($changed) {
            try {
                Cache::put($cacheKey, $data, 60 * 60 * 24);
            } catch (\Throwable $e) {
                // ignore
            }

            // Also update DB cache row so the admin form gets filled instantly next time.
            try {
                if (!$isBranchPayload($data)) {
                    DB::table('tax_lookup_caches')->updateOrInsert(
                        ['tax_code' => $taxCode],
                        [
                            'payload' => json_encode($data, JSON_UNESCAPED_UNICODE),
                            'source_url' => (string) ($data['source_url'] ?? ''),
                            'fetched_at' => now(),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $data;
    }

    private function lookupRepresentativeFromMasothue(string $taxCode, bool $debugMode): ?string
    {
        $taxCode = preg_replace('/\s+/', '', trim((string) $taxCode));
        if ($taxCode === '' || strlen($taxCode) < 8) return null;

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => 'https://masothue.com/',
        ];

        $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCode) . '&type=taxCode';

        try {
            $res = Http::timeout(15)->withHeaders($headers)->get($searchUrl);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$res->ok()) return null;
        if ($res->status() === 403) return null;

        $html = (string) $res->body();
        if ($html === '') return null;

        $lowerHtml = mb_strtolower($html);
        $blockedSignals = [
            'checking your browser before accessing',
            'cf-browser-verification',
            'cf-challenge-running',
            'why have i been blocked',
            'please enable cookies',
            'unusual traffic',
        ];
        foreach ($blockedSignals as $sig) {
            if ($sig !== '' && str_contains($lowerHtml, $sig)) return null;
        }

        // Masothue uses:
        // <tr itemprop="alumni" ...>
        //   <td>Người đại diện</td>
        //   <td><span itemprop="name"><a>NGUYỄN ...</a></span> ...</td>
        try {
            $dom = new \DOMDocument();
            $prev = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            $xpath = new \DOMXPath($dom);
            $nodes = $xpath->query('//tr[@itemprop="alumni"]//span[@itemprop="name"]//a');
            if ($nodes && $nodes->length > 0) {
                $el = $nodes->item(0);
                if ($el instanceof \DOMElement) {
                    $rep = trim((string) ($el->textContent ?? ''));
                    if ($rep !== '') return $rep;
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    private function lookupCompanyFieldsFromMasothue(string $taxCode, bool $debugMode): ?array
    {
        $taxCode = preg_replace('/\s+/', '', trim((string) $taxCode));
        if ($taxCode === '' || strlen($taxCode) < 8) return null;

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => 'https://masothue.com/',
        ];

        // Use Search result page (often includes legal fields block like in your ms31.html snippet).
        $searchUrl = 'https://masothue.com/Search/?q=' . urlencode($taxCode) . '&type=taxCode';

        try {
            $res = Http::timeout(15)->withHeaders($headers)->get($searchUrl);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$res->ok()) return null;
        if ($res->status() === 403) return null;

        $html = (string) $res->body();
        if ($html === '') return null;

        $lowerHtml = mb_strtolower($html);
        $blockedSignals = [
            'checking your browser before accessing',
            'cf-browser-verification',
            'cf-challenge-running',
            'why have i been blocked',
            'please enable cookies',
            'unusual traffic',
        ];
        foreach ($blockedSignals as $sig) {
            if ($sig !== '' && str_contains($lowerHtml, $sig)) return null;
        }

        try {
            $dom = new \DOMDocument();
            $prev = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            $xpath = new \DOMXPath($dom);

            $rep = '';
            $nRep = $xpath->query('//tr[@itemprop="alumni"]//span[@itemprop="name"]//a[1]');
            if ($nRep && $nRep->length > 0) {
                $el = $nRep->item(0);
                if ($el instanceof \DOMElement) $rep = trim((string) $el->textContent);
            }

            $phone = '';
            $nTel = $xpath->query('//td[@itemprop="telephone"]//span[contains(concat(" ", normalize-space(@class), " "), " copy ")][1]');
            if ($nTel && $nTel->length > 0) {
                $el = $nTel->item(0);
                if ($el instanceof \DOMElement) $phone = trim((string) $el->textContent);
            }
            // Normalize phone if wrapped or with extra spaces.
            if ($phone !== '') {
                if (preg_match('/\b(0\d{8,10})\b/u', $phone, $mPhone)) {
                    $phone = (string) ($mPhone[1] ?? $phone);
                } else {
                    $phone = trim($phone);
                }
            }

            $status = '';
            // In Masothue HTML, "Tình trạng" typically uses id="tax-status-html"
            $nSt = $xpath->query('//*[@id="tax-status-html"]//a[1]');
            if ($nSt && $nSt->length > 0) {
                $el = $nSt->item(0);
                if ($el instanceof \DOMElement) $status = trim((string) $el->textContent);
            } else {
                $nSt2 = $xpath->query('//*[@id="tax-status-html"][1]');
                if ($nSt2 && $nSt2->length > 0) {
                    $el2 = $nSt2->item(0);
                    if ($el2 instanceof \DOMElement) $status = trim((string) $el2->textContent);
                }
            }

            $biz = '';
            $nBiz = $xpath->query('//td[contains(., "Loại hình DN")]/following-sibling::td[1][1]');
            if ($nBiz && $nBiz->length > 0) {
                $el = $nBiz->item(0);
                if ($el instanceof \DOMElement) {
                    $biz = trim((string) $el->textContent);
                }
            }

            $fields = [
                'tax_id' => $taxCode,
                'representative' => $rep,
                'phone' => $phone,
                'company_status' => $status,
                'business_type' => $biz,
                'source_url' => $searchUrl,
            ];

            // Remove empty strings to reduce chance of “hasAny” gating elsewhere.
            foreach (['representative', 'phone', 'company_status', 'business_type'] as $k) {
                if (trim((string) ($fields[$k] ?? '')) === '') unset($fields[$k]);
            }

            return $fields;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Public JSON API (same family as checkout /api/tax-code). Usually stable vs scraping Masothue HTML.
     *
     * @see https://www.vietqr.io/en/danh-sach-api/tax-id-lookup
     */
    private function lookupTaxViaVietQr(string $taxCode): ?array
    {
        try {
            $res = Http::timeout(15)
                ->acceptJson()
                ->get('https://api.vietqr.io/v2/business/' . urlencode($taxCode));
        } catch (\Throwable $e) {
            return null;
        }

        if ($res->status() === 429) {
            return null;
        }

        if (!$res->ok()) {
            return null;
        }

        $json = $res->json();
        if (!is_array($json) || (string) ($json['code'] ?? '') !== '00') {
            return null;
        }

        $d = $json['data'] ?? null;
        if (!is_array($d)) {
            return null;
        }

        $name = trim((string) ($d['name'] ?? ''));
        $address = trim((string) ($d['address'] ?? ''));
        if ($name === '' && $address === '') {
            return null;
        }

        $tid = preg_replace('/\s+/', '', (string) ($d['id'] ?? $taxCode));

        return [
            'tax_id' => $tid,
            'name' => $name,
            'tax_address' => $address,
            'address' => $address,
            'company_status' => '',
            'representative' => '',
            'phone' => '',
            'active_date' => '',
            'managed_by' => '',
            'business_type' => '',
            'main_business' => '',
            'source_url' => 'https://api.vietqr.io/v2/business/' . urlencode($taxCode),
        ];
    }

    private function fallbackTaxLookupTracuuMasothue(string $taxCode, bool $debugMode, ?array &$debugOut = null)
    {
        $taxCode = preg_replace('/\s+/', '', trim((string) $taxCode));
        if ($taxCode === '' || strlen($taxCode) < 8) {
            if ($debugMode) {
                $debugOut = [
                    'source' => 'tracuu-masothue.com',
                    'attempts' => [],
                    'reason' => 'invalid_tax_code',
                ];
            }
            return false;
        }

        $searchCodes = [$taxCode];
        if (str_contains($taxCode, '-')) {
            $base = (string) explode('-', $taxCode, 2)[0];
            $base = preg_replace('/\s+/', '', trim($base));
            if ($base !== '' && $base !== $taxCode) {
                $searchCodes[] = $base;
            }
        }

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Upgrade-Insecure-Requests' => '1',
            'Referer' => 'https://tracuu-masothue.com/',
        ];

        $decode = function ($v) {
            $v = html_entity_decode((string) $v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $v = preg_replace('/\s+/u', ' ', trim($v));
            return $v;
        };

        $attempts = [];

        $cookieJar = new \GuzzleHttp\Cookie\CookieJar();
        $client = Http::timeout(15)
            ->withHeaders($headers)
            ->withOptions([
                'cookies' => $cookieJar,
                'allow_redirects' => true,
            ]);

        $tokenCacheKey = 'tracuu_masothue.token.v1';
        $token = '';
        try {
            $token = (string) Cache::get($tokenCacheKey, '');
        } catch (\Throwable $e) {
            $token = '';
        }

        $discoverToken = function (string $code) use (&$attempts, $client, $decode, $debugMode) {
            $code = preg_replace('/\s+/', '', trim((string) $code));
            $searchUrl = 'https://tracuu-masothue.com/?s=' . urlencode($code);
            try {
                $res = $client->get($searchUrl);
            } catch (\Throwable $e) {
                $attempts[] = ['url' => $searchUrl, 'status' => null, 'ok' => false, 'note' => 'token_search_exception'];
                return '';
            }

            $html = (string) $res->body();
            $attempts[] = [
                'url' => $searchUrl,
                'status' => $res->status(),
                'ok' => $res->ok(),
                'note' => 'token_search',
                'body_preview' => $debugMode ? mb_substr($decode($html), 0, 260) : null,
            ];
            if (!$res->ok()) return '';
            if ($html === '') return '';

            // Token often appears inside XHR URLs like: tracuu.php?name=...&token=...
            $patterns = [
                '/\btoken=([a-f0-9]{16,128})\b/iu',
                '/["\']token["\']\s*[:=]\s*["\']([a-f0-9]{16,128})["\']/iu',
                '/data-token\s*=\s*["\']([a-f0-9]{16,128})["\']/iu',
            ];

            $tokens = [];
            foreach ($patterns as $p) {
                if (preg_match_all($p, $html, $mm) && !empty($mm[1])) {
                    foreach ($mm[1] as $cand) {
                        $cand = $decode((string) $cand);
                        if ($cand === '') continue;
                        if (!preg_match('/^[a-f0-9]{16,128}$/iu', $cand)) continue;
                        $tokens[] = $cand;
                    }
                }
            }

            $token = '';
            if (!empty($tokens)) {
                $tokens = array_values(array_unique($tokens));
                usort($tokens, function ($a, $b) {
                    $la = strlen((string) $a);
                    $lb = strlen((string) $b);
                    if ($la === $lb) return 0;
                    return $la > $lb ? -1 : 1;
                });
                $token = (string) ($tokens[0] ?? '');
            }

            if ($token !== '') {
                $attempts[] = [
                    'url' => $searchUrl,
                    'status' => $res->status(),
                    'ok' => $res->ok(),
                    'note' => 'token_found',
                    'token_preview' => $debugMode ? mb_substr($token, 0, 16) : null,
                ];
                return $token;
            }

            return '';
        };

        $parseTracuuResponse = function (string $body, string $codeQueried) use ($decode) {
            $digitsWanted = preg_replace('/\D+/', '', (string) $codeQueried);

            $pickFirstByDigits = function (array $rows) use ($digitsWanted) {
                if ($digitsWanted === '') return $rows[0] ?? null;
                foreach ($rows as $r) {
                    $mst = (string) ($r['mst'] ?? $r['tax_id'] ?? $r['ma_so_thue'] ?? $r['MST'] ?? '');
                    if (preg_replace('/\D+/', '', $mst) === $digitsWanted) return $r;
                }
                return $rows[0] ?? null;
            };

            $json = null;
            try {
                $json = json_decode($body, true);
            } catch (\Throwable $e) {
                $json = null;
            }

            $row = null;
            if (is_array($json)) {
                if (isset($json['data']) && is_array($json['data'])) {
                    $row = $pickFirstByDigits($json['data']);
                } elseif (array_is_list($json)) {
                    $row = $pickFirstByDigits($json);
                } else {
                    $row = $json;
                }
            }

            if (is_array($row) && !empty($row)) {
                $taxId = (string) ($row['mst'] ?? $row['tax_id'] ?? $row['ma_so_thue'] ?? $row['MST'] ?? '');
                $name = (string) ($row['ten'] ?? $row['name'] ?? $row['ten_cong_ty'] ?? $row['company_name'] ?? '');
                $address = (string) ($row['diachi'] ?? $row['address'] ?? $row['dia_chi'] ?? '');
                $representative = (string) ($row['nguoidaidien'] ?? $row['representative'] ?? $row['nguoi_dai_dien'] ?? '');
                $status = (string) ($row['tinhtrang'] ?? $row['status'] ?? $row['tinh_trang'] ?? '');

                $taxId = $decode($taxId);
                $name = $decode($name);
                $address = $decode($address);
                $representative = $decode($representative);
                $status = $decode($status);

                return [
                    'tax_id' => $taxId !== '' ? $taxId : $codeQueried,
                    'name' => $name,
                    'tax_address' => $address,
                    'address' => $address,
                    'company_status' => $status,
                    'representative' => $representative,
                    'phone' => '',
                    'active_date' => '',
                    'managed_by' => '',
                    'business_type' => '',
                    'main_business' => '',
                ];
            }

            // Fallback: HTML table parsing (in case endpoint returns HTML snippet)
            $domMap = [];
            $title = '';
            try {
                $dom = new \DOMDocument();
                $prev = libxml_use_internal_errors(true);
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $body);
                libxml_clear_errors();
                libxml_use_internal_errors($prev);

                $xpath = new \DOMXPath($dom);
                $h1 = $xpath->query('//h1');
                if ($h1 && $h1->length > 0) {
                    $title = $decode((string) ($h1->item(0)->textContent ?? ''));
                }

                $trs = $xpath->query('//tr');
                if ($trs) {
                    foreach ($trs as $tr) {
                        if (!($tr instanceof \DOMElement)) continue;
                        $cells = [];
                        foreach ($tr->childNodes as $child) {
                            if (!($child instanceof \DOMElement)) continue;
                            $tag = strtolower($child->tagName);
                            if ($tag === 'td' || $tag === 'th') {
                                $cells[] = $decode((string) ($child->textContent ?? ''));
                            }
                        }
                        if (count($cells) >= 2) {
                            $k = trim((string) ($cells[0] ?? ''));
                            $v = trim((string) ($cells[1] ?? ''));
                            $k = preg_replace('/\s*:\s*$/u', '', $k);
                            if ($k !== '') {
                                $domMap[$k] = $v;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                $domMap = [];
                $title = '';
            }

            if (empty($domMap) && $title === '') {
                return null;
            }

            $pick = function (string $label) use ($domMap) {
                foreach ($domMap as $k => $v) {
                    if (mb_stripos((string) $k, $label) !== false) {
                        return (string) $v;
                    }
                }
                return '';
            };

            $taxId = $pick('Mã số thuế');
            if ($taxId === '') $taxId = $codeQueried;
            $name = $title;
            if ($name !== '') {
                $name = preg_replace('/^\s*' . preg_quote($codeQueried, '/') . '\s*\-\s*/u', '', (string) $name);
            }

            return [
                'tax_id' => $taxId,
                'name' => $name,
                'tax_address' => $pick('Địa chỉ Thuế') !== '' ? $pick('Địa chỉ Thuế') : $pick('Địa chỉ'),
                'address' => $pick('Địa chỉ'),
                'company_status' => $pick('Tình trạng'),
                'representative' => $pick('Người đại diện'),
                'phone' => '',
                'active_date' => $pick('Ngày hoạt động'),
                'managed_by' => $pick('Quản lý'),
                'business_type' => $pick('Loại hình'),
                'main_business' => $pick('Ngành nghề'),
            ];
        };

        $found = null;
        $sourceUrl = '';
        foreach ($searchCodes as $code) {
            if ($token === '') {
                $token = $discoverToken($code);
                if ($token !== '') {
                    try {
                        Cache::put($tokenCacheKey, $token, 60 * 60 * 6);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }

            $endpointUrl = 'https://tracuu-masothue.com/tracuu.php?name=' . urlencode($code);
            if ($token !== '') {
                $endpointUrl .= '&token=' . urlencode($token);
            }

            $xhrHeaders = $headers;
            $xhrHeaders['Accept'] = '*/*';
            $xhrHeaders['X-Requested-With'] = 'XMLHttpRequest';
            $xhrHeaders['Referer'] = 'https://tracuu-masothue.com/?s=' . urlencode($code);

            try {
                $res = $client->withHeaders($xhrHeaders)->get($endpointUrl);
            } catch (\Throwable $e) {
                $attempts[] = ['url' => $endpointUrl, 'status' => null, 'ok' => false, 'note' => 'xhr_exception'];
                continue;
            }

            $body = (string) $res->body();
            $trimBody = trim($body);

            // If token is required/expired, endpoint may return plain "403" with HTTP 200.
            if (($trimBody === '403' || $trimBody === 'Forbidden') && $token !== '') {
                $attempts[] = [
                    'url' => $endpointUrl,
                    'status' => $res->status(),
                    'ok' => $res->ok(),
                    'note' => 'xhr_token_rejected',
                    'body_preview' => $debugMode ? mb_substr($decode($body), 0, 220) : null,
                ];

                // Refresh token and retry once.
                $token = '';
                try {
                    Cache::forget($tokenCacheKey);
                } catch (\Throwable $e) {
                    // ignore
                }

                $token = $discoverToken($code);
                if ($token !== '') {
                    try {
                        Cache::put($tokenCacheKey, $token, 60 * 60 * 6);
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                $endpointUrl2 = 'https://tracuu-masothue.com/tracuu.php?name=' . urlencode($code);
                if ($token !== '') {
                    $endpointUrl2 .= '&token=' . urlencode($token);
                }

                try {
                    $res = $client->withHeaders($xhrHeaders)->get($endpointUrl2);
                    $body = (string) $res->body();
                    $trimBody = trim($body);
                    $endpointUrl = $endpointUrl2;
                } catch (\Throwable $e) {
                    $attempts[] = ['url' => $endpointUrl2, 'status' => null, 'ok' => false, 'note' => 'xhr_retry_exception'];
                    continue;
                }
            }

            $attempts[] = [
                'url' => $endpointUrl,
                'status' => $res->status(),
                'ok' => $res->ok(),
                'note' => 'xhr',
                'body_preview' => $debugMode ? mb_substr($decode($body), 0, 220) : null,
            ];

            if (!$res->ok() || $body === '') {
                continue;
            }

            $parsed = $parseTracuuResponse($body, $code);
            if (is_array($parsed)) {
                $isEmpty = ($parsed['name'] ?? '') === ''
                    && ($parsed['tax_address'] ?? '') === ''
                    && ($parsed['address'] ?? '') === ''
                    && ($parsed['representative'] ?? '') === ''
                    && ($parsed['company_status'] ?? '') === '';
                if (!$isEmpty) {
                    $found = $parsed;
                    $sourceUrl = $endpointUrl;
                    break;
                }
            }
        }

        if (!$found) {
            if ($debugMode) {
                $debugOut = [
                    'source' => 'tracuu-masothue.com',
                    'attempts' => $attempts,
                    'reason' => 'not_found',
                ];
            }
            return false;
        }

        $payload = $found;
        $payload['source_url'] = $sourceUrl;

        $isEmpty = ($payload['name'] ?? '') === ''
            && ($payload['tax_address'] ?? '') === ''
            && ($payload['address'] ?? '') === ''
            && ($payload['representative'] ?? '') === ''
            && ($payload['company_status'] ?? '') === '';

        if ($isEmpty) {
            if ($debugMode) {
                $debugOut = [
                    'source' => 'tracuu-masothue.com',
                    'attempts' => $attempts,
                    'reason' => 'empty_payload',
                ];
            }
            return false;
        }

        if ($debugMode) {
            $debugOut = [
                'source' => 'tracuu-masothue.com',
                'attempts' => $attempts,
                'reason' => 'ok',
            ];
        }
        return $payload;
    }

    /**
     * JSON gợi ý khách hàng (tên / MST / email / SĐT) — dùng trên form phiếu mượn, v.v.
     */
    public function lookup(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $compact = preg_replace('/\s+/u', '', $q) ?? $q;
        $taxLike = is_string($compact) && preg_match('/^[\d\-]{8,}$/', $compact) === 1;
        $digits = preg_replace('/\D+/', '', $compact);

        if (!$taxLike && mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query = Customer::query()
            ->select(['id', 'name', 'tax_id', 'email', 'phone', 'invoice_recipient', 'representative']);

        if ($taxLike && $digits !== '') {
            $query->where(function ($sub) use ($digits, $compact) {
                $sub->where('tax_id', 'like', '%' . $digits . '%');
                if ($compact !== $digits) {
                    $sub->orWhere('tax_id', 'like', '%' . $compact . '%');
                }
            });
        } else {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('tax_id', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%');
            });
        }

        $customers = $query
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        $items = $customers->map(function (Customer $c) {
            return [
                'id' => $c->id,
                'name' => (string) ($c->name ?? ''),
                'tax_id' => (string) ($c->tax_id ?? ''),
                'email' => (string) ($c->email ?? ''),
                'phone' => (string) ($c->phone ?? ''),
                'invoice_recipient' => (string) ($c->invoice_recipient ?? ''),
                'representative' => (string) ($c->representative ?? ''),
            ];
        });

        return response()->json($items->values()->all());
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        // Keyword search (global)
        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('tax_id', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('representative', 'like', "%{$q}%");
            });
        }

        // Advanced filters
        if ($request->filled('status')) {
            $status = trim((string) $request->query('status'));
            $query->where('company_status', 'like', "%{$status}%");
        }

        if ($request->filled('biz_type')) {
            $bizType = trim((string) $request->query('biz_type'));
            $query->where('business_type', 'like', "%{$bizType}%");
        }

        if ($request->filled('rep')) {
            $rep = trim((string) $request->query('rep'));
            $query->where('representative', 'like', "%{$rep}%");
        }

        $customers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function importForm()
    {
        return view('admin.customers.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        try {
            $data = Excel::toArray([], $file);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (stripos($msg, 'not recognised as an OLE file') !== false) {
                return back()->with('error', 'File Excel không đúng định dạng. Nếu bạn đang dùng file .xls, vui lòng lưu lại thành .xlsx rồi import lại.');
            }

            return back()->with('error', 'Không đọc được file Excel. Vui lòng kiểm tra lại file và thử lại.');
        }
        $rows = $data[0] ?? [];

        $headerRowIndex = 0;
        foreach ($rows as $i => $row) {
            if (!empty(array_filter($row))) {
                $headerRowIndex = $i;
                break;
            }
        }

        $headerRaw = $rows[$headerRowIndex] ?? [];
        $header = array_map(function ($h) {
            return trim((string) $h);
        }, $headerRaw);

        unset($rows[$headerRowIndex]);

        $map = [
            'Tên khách hàng' => 'name',
            'MST/CCCD chủ hộ' => 'tax_id',
            'Địa chỉ' => 'address',
            'Người nhận HĐ' => 'invoice_recipient',
            'Email' => 'email',
            'Số điện thoại' => 'phone',
        ];

        $imported = 0;

        foreach ($rows as $row) {
            if (!is_array($row) || empty(array_filter($row))) {
                continue;
            }

            $rowData = [];
            foreach ($map as $excelHeader => $field) {
                $idx = array_search($excelHeader, $header);
                if ($idx !== false) {
                    $rowData[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
                }
            }

            $name = trim((string) ($rowData['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $taxId = trim((string) ($rowData['tax_id'] ?? ''));
            $payload = [
                'name' => $name,
                'tax_id' => $taxId !== '' ? $taxId : null,
                'address' => ($rowData['address'] ?? '') !== '' ? $rowData['address'] : null,
                'invoice_recipient' => ($rowData['invoice_recipient'] ?? '') !== '' ? $rowData['invoice_recipient'] : null,
                'email' => ($rowData['email'] ?? '') !== '' ? $rowData['email'] : null,
                'phone' => ($rowData['phone'] ?? '') !== '' ? $rowData['phone'] : null,
            ];

            if ($taxId !== '') {
                Customer::updateOrCreate(['tax_id' => $taxId], $payload);
            } else {
                Customer::create($payload);
            }

            $imported++;
        }

        ActivityLogger::log('customer.import_excel', null, 'Import khách hàng từ Excel', [
            'imported' => $imported,
        ], $request);

        return redirect()->route('admin.customers.index')->with('success', 'Đã import ' . $imported . ' khách hàng!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'tax_address' => 'nullable|string',
            'address' => 'nullable|string',
            'invoice_recipient' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company_status' => 'nullable|string|max:255',
            'representative' => 'nullable|string|max:255',
            'managed_by' => 'nullable|string|max:255',
            'active_date' => 'nullable|date',
            'business_type' => 'nullable|string|max:255',
            'main_business' => 'nullable|string',
        ]);

        $validated = $this->filterExistingCustomerColumns($validated);
        $customer = Customer::create($validated);

        ActivityLogger::log('customer.create', $customer, 'Tạo khách hàng: ' . ($customer->name ?? ''), [
            'name' => $customer->name ?? null,
            'tax_id' => $customer->tax_id ?? null,
        ], $request);

        return redirect()->route('admin.customers.index')->with('success', 'Đã tạo khách hàng!');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'tax_address' => 'nullable|string',
            'address' => 'nullable|string',
            'invoice_recipient' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company_status' => 'nullable|string|max:255',
            'representative' => 'nullable|string|max:255',
            'managed_by' => 'nullable|string|max:255',
            'active_date' => 'nullable|date',
            'business_type' => 'nullable|string|max:255',
            'main_business' => 'nullable|string',
        ]);

        $validated = $this->filterExistingCustomerColumns($validated);

        $before = $customer->only(['name', 'tax_id', 'tax_address', 'address', 'invoice_recipient', 'email', 'phone', 'company_status', 'representative', 'managed_by', 'active_date', 'business_type', 'main_business']);

        $customer->update($validated);

        $after = $customer->fresh()->only(['name', 'tax_id', 'tax_address', 'address', 'invoice_recipient', 'email', 'phone', 'company_status', 'representative', 'managed_by', 'active_date', 'business_type', 'main_business']);

        ActivityLogger::log('customer.update', $customer, 'Cập nhật khách hàng: ' . ($customer->name ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Đã cập nhật khách hàng!');
    }

    public function destroy(Customer $customer, Request $request)
    {
        ActivityLogger::log('customer.delete', $customer, 'Xóa khách hàng: ' . ($customer->name ?? ''), [
            'name' => $customer->name ?? null,
            'tax_id' => $customer->tax_id ?? null,
        ], $request);

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Đã xóa khách hàng!');
    }
}
