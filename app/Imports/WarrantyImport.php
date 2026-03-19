<?php

namespace App\Imports;

use App\Models\Warranty;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
 

class WarrantyImport implements ToModel, WithHeadingRow, WithEvents, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    private int $successCount = 0;
    private int $errorCount = 0;
    private array $errorsBag = [];
    private ?string $currentSheetName = null;
    
    // Lưu giá trị từ row trước để xử lý merged cells
    private ?string $lastCustomerName = null;
    private ?string $lastDate = null;

    public function model(array $row)
    {
        try {
            // Normalize keys để xử lý khoảng trắng và ký tự đặc biệt
            $normalizeKey = function($key) {
                return trim(preg_replace('/\s+/', ' ', $key));
            };
            
            // Normalize tất cả keys trong row
            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $normalizedKey = $normalizeKey($key);
                $normalizedRow[$normalizedKey] = $value;
                // Giữ cả key gốc
                if ($normalizedKey !== $key) {
                    $normalizedRow[$key] = $value;
                }
            }
            
            $get = function (array $keys) use ($row, $normalizedRow, $normalizeKey) {
                // Thử tìm trong cả row gốc và normalized row
                foreach ($keys as $k) {
                    $normalizedK = $normalizeKey($k);
                    
                    // Thử key gốc
                    if (array_key_exists($k, $row) && filled($row[$k])) {
                        return trim((string) $row[$k]);
                    }
                    
                    // Thử normalized key
                    if (array_key_exists($normalizedK, $normalizedRow) && filled($normalizedRow[$normalizedK])) {
                        return trim((string) $normalizedRow[$normalizedK]);
                    }
                    
                    // Thử tìm không phân biệt hoa thường - exact match
                    foreach ($row as $rowKey => $rowValue) {
                        if (empty($rowKey)) continue;
                        $normalizedRowKey = $normalizeKey($rowKey);
                        if (strcasecmp($normalizedRowKey, $normalizedK) === 0 && filled($rowValue)) {
                            return trim((string) $rowValue);
                        }
                    }
                    
                    // Thử tìm partial match (chứa từ khóa) - chỉ cho customer name
                    if (stripos($k, 'CÔNG TY') !== false || stripos($k, 'XUẤT') !== false) {
                        foreach ($row as $rowKey => $rowValue) {
                            if (empty($rowKey)) continue;
                            $normalizedRowKey = $normalizeKey($rowKey);
                            // Kiểm tra nếu rowKey chứa các từ khóa quan trọng
                            if ((stripos($normalizedRowKey, 'XUẤT') !== false && 
                                 stripos($normalizedRowKey, 'CÔNG TY') !== false) ||
                                (stripos($normalizedRowKey, 'XUAT') !== false && 
                                 stripos($normalizedRowKey, 'CONG TY') !== false)) {
                                if (filled($rowValue)) {
                                    return trim((string) $rowValue);
                                }
                            }
                        }
                    }
                }
                return null;
            };

            // Hỗ trợ nhiều tên cột khác nhau - ưu tiên "XUẤT CHO CÔNG TY" trước
            $serial = $get(['SERI', 'seri', 'Số seri', 'SỐ SERI', 'SỐ SERI (SN)', 'so_seri', 'serial', 'serial_number']);
            
            // Tìm tên khách hàng - thử nhiều biến thể, ưu tiên "XUẤT CHO CÔNG TY"
            // Excel có thể convert thành "xuat_cho_cong_ty" (chữ thường, không dấu, có dấu gạch dưới)
            $customerName = $get([
                'xuat_cho_cong_ty',      // Excel convert format (chữ thường, không dấu, có dấu gạch dưới) - ƯU TIÊN
                'XUAT_CHO_CONG_TY',      // Chữ hoa, không dấu, có dấu gạch dưới
                'XUẤT CHO CÔNG TY',      // Chính xác như trong Excel (có dấu, có khoảng trắng)
                'XUẤT CHO CÔNG TY ',     // Có khoảng trắng cuối
                ' XUẤT CHO CÔNG TY',     // Có khoảng trắng đầu
                'XUAT CHO CONG TY',       // Không dấu, có khoảng trắng
                'XUAT CHO CONG TY ',      // Không dấu + khoảng trắng
                'Xuất cho công ty',      // Có dấu, chữ thường đầu
                'xuất cho công ty',       // Tất cả chữ thường
                'CÔNG TY', 
                'cong_ty', 
                'Công ty', 
                'Tên khách hàng', 
                'ten_khach_hang', 
                'customer_name'
            ]);
            
            // Nếu vẫn không tìm thấy, tự động tìm cột có chứa "XUẤT" và "CÔNG TY"
            // Excel có thể convert "XUẤT CHO CÔNG TY" thành "xuat_cho_cong_ty" (chữ thường, không dấu, có dấu gạch dưới)
            if (empty($customerName)) {
                foreach ($row as $key => $value) {
                    // Bỏ qua các key rỗng hoặc null
                    if (empty($key)) continue;
                    
                    // Thử key gốc trước (có thể là "xuat_cho_cong_ty")
                    $keyStr = (string)$key;
                    if (stripos($keyStr, 'xuat') !== false && 
                        (stripos($keyStr, 'cong_ty') !== false || stripos($keyStr, 'cong ty') !== false || stripos($keyStr, 'công ty') !== false)) {
                        if (filled($value)) {
                            $customerName = trim((string) $value);
                            break;
                        }
                    }
                    
                    // Thử normalized key
                    $normalizedKey = strtoupper(trim(preg_replace('/[\s_]+/', ' ', (string)$key)));
                    // Tìm cột có chứa cả "XUẤT" và "CÔNG TY" (hoặc không dấu)
                    $hasXuat = (stripos($normalizedKey, 'XUẤT') !== false || stripos($normalizedKey, 'XUAT') !== false);
                    $hasCongTy = (stripos($normalizedKey, 'CÔNG TY') !== false || stripos($normalizedKey, 'CONG TY') !== false);
                    
                    if ($hasXuat && $hasCongTy) {
                        // Ưu tiên giá trị không rỗng
                        if (filled($value)) {
                            $customerName = trim((string) $value);
                            break;
                        }
                    }
                }
                
                // Nếu vẫn không có, thử tìm chỉ cần "CÔNG TY" hoặc "XUẤT"
                if (empty($customerName)) {
                    foreach ($row as $key => $value) {
                        if (empty($key)) continue;
                        $normalizedKey = strtoupper(trim(preg_replace('/[\s_]+/', ' ', (string)$key)));
                        if ((stripos($normalizedKey, 'CÔNG TY') !== false || stripos($normalizedKey, 'CONG TY') !== false) && filled($value)) {
                            $customerName = trim((string) $value);
                            break;
                        }
                    }
                }
            }
            
            // Xử lý merged cells: Nếu không có customer name, dùng giá trị từ row trước
            if (empty($customerName) && !empty($this->lastCustomerName)) {
                $customerName = $this->lastCustomerName;
            } else if (!empty($customerName)) {
                // Lưu giá trị mới cho các row sau
                $this->lastCustomerName = $customerName;
            }
            
            // Debug: Log để kiểm tra nếu không tìm thấy customer name
            if (empty($customerName) && !empty($serial)) {
                // Log để debug - chỉ log lần đầu tiên
                static $logged = false;
                if (!$logged) {
                    $allKeys = array_keys($row);
                    \Log::info('Warranty Import - All available column keys:', $allKeys);
                    \Log::info('Warranty Import - Full row data:', $row);
                    \Log::info('Warranty Import - Keys with values:', array_filter($row, function($v) { return filled($v); }));
                    
                    // Tìm các keys có chứa "CÔNG TY" hoặc "XUẤT"
                    $matchingKeys = array_filter($allKeys, function($key) {
                        $normalized = trim(preg_replace('/\s+/', ' ', strtoupper($key)));
                        return stripos($normalized, 'CÔNG TY') !== false || 
                               stripos($normalized, 'XUẤT') !== false ||
                               stripos($normalized, 'CONG TY') !== false ||
                               stripos($normalized, 'XUAT') !== false;
                    });
                    if (!empty($matchingKeys)) {
                        \Log::info('Warranty Import - Keys matching CÔNG TY/XUẤT:', array_values($matchingKeys));
                        // Thử lấy giá trị từ key đầu tiên
                        $firstKey = reset($matchingKeys);
                        \Log::info('Warranty Import - Value from first matching key:', ['key' => $firstKey, 'value' => $row[$firstKey] ?? null]);
                    }
                    $logged = true;
                }
            }
            // Tên sản phẩm / model
            $productName = $get(['Tên sản phẩm', 'ten_san_pham']);
            // Nếu file không có cột tên sản phẩm thì dùng tên sheet làm model
            if (empty($productName) && !empty($this->currentSheetName)) {
                $productName = trim((string)$this->currentSheetName);
            }
            $phone = $get(['Số điện thoại', 'so_dien_thoai', 'Điện thoại']);
            $email = $get(['Email', 'email']);
            $address = $get(['Địa chỉ', 'dia_chi']);
            $dateInput = $get(['NGÀY', 'ngay', 'Ngày', 'Ngày mua', 'ngay_mua', 'Ngày bắt đầu bảo hành', 'ngay_bat_dau_bao_hanh']);
            
            // Xử lý công thức Excel: Nếu giá trị là công thức (bắt đầu bằng "="), 
            // thư viện có thể trả về chuỗi công thức thay vì giá trị đã tính
            // Trong trường hợp này, dùng giá trị từ row trước (logic merged cells)
            // Vì công thức thường tham chiếu đến cell trước đó (như =C7 tham chiếu đến C7)
            if (!empty($dateInput)) {
                $dateInputStr = trim((string)$dateInput);
                // Nếu là công thức Excel (bắt đầu bằng "=" và không phải số), 
                // bỏ qua và dùng giá trị từ row trước
                if (strpos($dateInputStr, '=') === 0 && strlen($dateInputStr) > 1 && !is_numeric($dateInputStr)) {
                    // Nếu có giá trị từ row trước, dùng nó
                    if (!empty($this->lastDate)) {
                        $dateInput = $this->lastDate;
                    } else {
                        // Nếu không có giá trị từ row trước, bỏ qua (sẽ dùng now() trong parseDate)
                        $dateInput = null;
                    }
                } else {
                    // Nếu không phải công thức, lưu giá trị mới
                    $this->lastDate = $dateInput;
                }
            }
            
            // Xử lý merged cells cho ngày: Nếu không có ngày, dùng giá trị từ row trước
            if (empty($dateInput) && !empty($this->lastDate)) {
                $dateInput = $this->lastDate;
            }
            $periodMonthsInput = $get(['Bảo hành', 'bao_hanh', 'Thời hạn (tháng)']);
            $notes = $get(['Ghi chú', 'ghi_chu']);
            
            // Bỏ qua cột Số lượng - không xử lý

            // Chỉ cần số seri là bắt buộc
            if (empty($serial)) {
                $this->errorCount++;
                $this->errorsBag[] = 'Thiếu Số seri';
                return null;
            }

            // Kiểm tra xem serial đã tồn tại chưa
            if (Warranty::where('serial_number', $serial)->exists()) {
                $this->errorCount++;
                $this->errorsBag[] = "Số seri {$serial} đã tồn tại";
                return null;
            }

            // Không tạo sản phẩm mới khi import bảo hành, chỉ lưu tên model (productName) vào cột model_name
            $productId = null;

            // Parse ngày với nhiều format khác nhau
            // Hỗ trợ tất cả các công thức Excel: =C6, =D6, =E6, =A1, =B2, v.v.
            // Thư viện Maatwebsite\Excel sẽ tự động tính toán tất cả công thức và trả về giá trị đã tính
            $parseDate = function ($value) {
                if (!$value) return null;
                
                // Thư viện Maatwebsite\Excel mặc định đã tự động tính toán TẤT CẢ công thức Excel
                // Bất kể công thức tham chiếu đến cột nào (=C6, =D6, =E6, =A1, v.v.)
                // thư viện sẽ tự động tính toán và trả về giá trị đã tính toán (ví dụ: "14/8/2023")
                // Nếu vẫn còn công thức dạng string (bắt đầu bằng "="), bỏ qua
                $valueStr = trim((string)$value);
                if (strpos($valueStr, '=') === 0 && strlen($valueStr) > 1 && !is_numeric($valueStr)) {
                    // Nếu là công thức chưa được tính (hiếm khi xảy ra), bỏ qua
                    return null;
                }
                
                // DEBUG: Log để kiểm tra giá trị nhận được (có thể comment lại sau khi test xong)
                // Uncomment dòng dưới để xem giá trị Excel trả về:
                // \Log::info('Parse Date - Original value:', ['value' => $value, 'type' => gettype($value), 'string' => $valueStr]);
                
                // ƯU TIÊN: Xử lý text format trước để tránh vấn đề Excel parse sai format
                // Nếu giá trị có dạng text chứa / hoặc -, thử parse theo DD/MM/YYYY trước
                $originalValue = $value;
                $valueStrClean = preg_replace('/\s+/', '', $valueStr);
                
                // Kiểm tra nếu là format text date (có chứa / hoặc -)
                if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $valueStrClean)) {
                    // Xử lý text format trước (sẽ được xử lý ở phần sau)
                    // Tạm thời bỏ qua phần này, sẽ xử lý ở dưới
                } else {
                    // Nếu không phải text format, xử lý serial date
                    if (is_numeric($value)) {
                        try {
                            // Kiểm tra nếu là Excel serial date (thường > 1 và < 1000000)
                            if ($value > 1 && $value < 1000000) {
                                $excelDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));

                                return $excelDate;
                            }
                        } catch (\Throwable $e) {
                            // Nếu không phải Excel date, tiếp tục xử lý như string
                        }
                    }
                }
                
                // Loại bỏ các ký tự không cần thiết (nếu chưa clean ở trên)
                if (!isset($valueStrClean)) {
                    $valueStrClean = preg_replace('/\s+/', '', $valueStr);
                }
                $valueStr = $valueStrClean;
                
                // Xử lý format "DD-Mon" hoặc "DD-Mon-YY" (ví dụ: "16-Apr", "12-May", "5-Dec")
                if (preg_match('/^(\d{1,2})-([A-Za-z]{3})(?:-(\d{2,4}))?$/i', $valueStr, $matches)) {
                    $day = (int)$matches[1];
                    $monthName = ucfirst(strtolower($matches[2]));
                    $year = isset($matches[3]) ? (int)$matches[3] : date('Y');
                    
                    // Xử lý năm 2 chữ số
                    if ($year < 100) {
                        $year = $year < 50 ? 2000 + $year : 1900 + $year;
                    }
                    
                    $monthMap = [
                        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                        'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
                    ];
                    
                    if (isset($monthMap[$monthName])) {
                        try {
                            return Carbon::create($year, $monthMap[$monthName], $day);
                        } catch (\Throwable $e) {}
                    }
                }
                
                // Xử lý format d/m/yyyy hoặc d/m/yy (ví dụ: 21/4/2025, 14/8/2023, 10/2/2025)
                // Pattern: số/số/số (có thể có 1-2 chữ số cho ngày, 1-2 chữ số cho tháng, 2-4 chữ số cho năm)
                // ƯU TIÊN ĐỊNH DẠNG VIỆT NAM (DD/MM/YYYY) theo format hiển thị trong Excel
                // Format hiển thị trong Excel: "21/4/2025" = ngày 21 tháng 4 năm 2025 (DD/M/YYYY)
                if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/', $valueStr, $matches)) {
                    $first = (int)$matches[1];
                    $second = (int)$matches[2];
                    $year = (int)$matches[3];
                    
                    // Xử lý năm 2 chữ số
                    if ($year < 100) {
                        $year = $year < 50 ? 2000 + $year : 1900 + $year;
                    }
                    
                    // Ưu tiên VN: hiểu chuỗi theo DD/MM trước.
                    // Chỉ fallback sang MM/DD nếu DD/MM không hợp lệ (ví dụ: 13/20/2025).
                    if ($first >= 1 && $first <= 31 && $second >= 1 && $second <= 12 && checkdate($second, $first, $year)) {
                        try {
                            return Carbon::create($year, $second, $first);
                        } catch (\Throwable $e) {
                        }
                    }

                    // Fallback: thử MM/DD nếu DD/MM không hợp lệ
                    if ($first >= 1 && $first <= 12 && $second >= 1 && $second <= 31 && checkdate($first, $second, $year)) {
                        try {
                            return Carbon::create($year, $first, $second);
                        } catch (\Throwable $e) {
                        }
                    }
                }
                
                // Xử lý format d-m-yyyy hoặc d-m-yy
                // ƯU TIÊN ĐỊNH DẠNG VIỆT NAM (DD-MM-YYYY) thay vì US (MM-DD-YYYY)
                if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{2,4})$/', $valueStr, $matches)) {
                    $first = (int)$matches[1];
                    $second = (int)$matches[2];
                    $year = (int)$matches[3];
                    
                    if ($year < 100) {
                        $year = $year < 50 ? 2000 + $year : 1900 + $year;
                    }
                    
                    // Ưu tiên VN: DD-MM trước
                    if ($first >= 1 && $first <= 31 && $second >= 1 && $second <= 12 && checkdate($second, $first, $year)) {
                        try {
                            return Carbon::create($year, $second, $first);
                        } catch (\Throwable $e) {
                        }
                    }

                    // Fallback: thử MM-DD nếu DD-MM không hợp lệ
                    if ($first >= 1 && $first <= 12 && $second >= 1 && $second <= 31 && checkdate($first, $second, $year)) {
                        try {
                            return Carbon::create($year, $first, $second);
                        } catch (\Throwable $e) {
                        }
                    }
                }
                
                // BỎ QUA phần parse tự động nếu đã xử lý format số/số/số ở trên
                // Vì logic ở trên đã xử lý đúng với swap, không cần parse lại bằng Carbon
                // Chỉ parse tự động nếu không match format số/số/số
                $isNumberFormat = preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $valueStr);
                
                if (!$isNumberFormat) {
                    // Chỉ parse tự động với các format khác (không phải số/số/số)
                    // Ưu tiên format dd/mm/yyyy (Việt Nam) - với cả d/m/Y cho single digit
                    foreach (['d/m/Y', 'd/m/y', 'j/n/Y', 'j/n/y', 'd-m-Y', 'd-m-y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'd/M/Y', 'd-M-Y'] as $fmt) {
                        try { 
                            // Chuẩn hóa separator về /
                            $normalized = str_replace(['.', '-'], '/', $valueStr);
                            $date = Carbon::createFromFormat($fmt, $normalized);
                            if ($date && $date->year >= 1900 && $date->year <= 2100) {
                                // Chỉ dùng format DD/MM (VN), bỏ qua MM/DD
                                if (strpos($fmt, 'd') === 0 || strpos($fmt, 'j') === 0 || strpos($fmt, 'Y') === 0 || strpos($fmt, 'y') === 0) {
                                    return $date;
                                }
                            }
                        } catch (\Throwable $e) {}
                    }
                    
                    // Thử parse tự động với Carbon cho các format khác
                    try { 
                        $parsed = Carbon::parse($valueStr);
                        if ($parsed && $parsed->year >= 1900 && $parsed->year <= 2100) {
                            return $parsed;
                        }
                    } catch (\Throwable $e) {}
                }
                
                return null;
            };

            // Nếu có ngày từ file, dùng làm cả purchase_date và warranty_start_date
            $purchaseDate = $parseDate($dateInput) ?? now();
            $startDate = $purchaseDate; // Cùng 1 ngày như yêu cầu
            
            // Thời hạn bảo hành luôn là 12 tháng
            $periodMonths = 12;
            $endDate = (clone $startDate)->addMonths($periodMonths);

            // Tự động sinh mã hóa đơn
            $invoiceNumber = 'HD' . date('Ymd') . str_pad(Warranty::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            $warranty = Warranty::create([
                'serial_number' => $serial,
                'product_id' => $productId,
                'model_name' => $productName,
                'customer_name' => $customerName,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'customer_address' => $address,
                'purchase_date' => $purchaseDate->toDateString(),
                'warranty_start_date' => $startDate->toDateString(),
                'warranty_end_date' => $endDate->toDateString(),
                'warranty_period_months' => $periodMonths,
                'invoice_number' => $invoiceNumber,
                'notes' => $notes,
                'status' => $endDate->isFuture() ? 'active' : 'expired',
            ]);

            // Log status change
            $warranty->statuses()->create([
                'status' => 'created',
                'notes' => 'Tạo bảo hành từ import',
                'changed_by' => 'admin'
            ]);

            $this->successCount++;
            return $warranty;
        } catch (\Throwable $e) {
            $this->errorCount++;
            $this->errorsBag[] = $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            '*.SERI' => 'required',
            '*.Số seri' => 'required',
        ];
    }

    public function getSuccessCount(): int { return $this->successCount; }
    public function getErrorCount(): int { return $this->errorCount; }
    public function getErrors(): array { return $this->errorsBag; }

    /**
     * Lấy tên sheet hiện tại để dùng làm tên model khi import (ví dụ: K50PRO, K20PRO, ...)
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->currentSheetName = $event->getSheet()->getTitle();
            },
        ];
    }
}


