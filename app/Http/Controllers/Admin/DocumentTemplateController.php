<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Support\DocxTemplateRenderer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DocumentTemplateController extends Controller
{
    private const SUPPORTED_FIELDS = [
        'QuoteCode','SalesOrderCode','CustomerName','TaxCode','Address','InvoiceAddress','ReceiverAddress','ContactPerson','Phone','Email','Date',
        'StaffCode','CreatedBy','Warranty','SubTotal','VatPercent','VatAmount','DiscountPercent','TotalAmount','TotalAmountInWords',
        '#Items','/Items',
        'Item.No','Item.Name','Item.Category','ItemCategory','Category','Item.Unit','Item.Quantity','Item.UnitPrice','Item.LineTotal','Item.Image',
        'PdfHtml',
    ];
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type', ''));
        $query = DocumentTemplate::query()->orderByDesc('created_at');
        if ($type !== '') {
            if ($type === 'sales_order') {
                $query->whereIn('type', ['sales_order', 'shared']);
            } elseif ($type === 'quote') {
                $query->whereIn('type', ['quote', 'shared']);
            } else {
                $query->where('type', $type);
            }
        }

        $templates = $query->paginate(20)->withQueryString();

        return view('admin.document_templates.index', compact('templates', 'type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quote,sales_order,invoice,pdf'],
            'file' => ['required', 'file', 'mimes:docx,xlsx,xls,pdf,html,htm'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $fieldError = $this->validateTemplateFields($request->file('file')->getRealPath(), (string) $request->file('file')->getClientOriginalExtension());
        if ($fieldError) {
            return back()->withInput()->with('error', $fieldError);
        }

        $uploadedFile = $request->file('file');
        $fileType = strtolower((string) $uploadedFile->getClientOriginalExtension());
        $path = $uploadedFile->store('document_templates');

        if (!empty($validated['is_default'])) {
            DocumentTemplate::query()->where('type', $validated['type'])->update(['is_default' => false]);
        }

        DocumentTemplate::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'file_path' => $path,
            'file_type' => $fileType,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        return back()->with('success', 'Đã tải mẫu in lên thành công.');
    }

    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quote,sales_order,invoice,pdf'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'mimes:docx,xlsx,xls,pdf,html,htm'],
        ]);

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fieldError = $this->validateTemplateFields($uploadedFile->getRealPath(), (string) $uploadedFile->getClientOriginalExtension());
            if ($fieldError) {
                return back()->withInput()->with('error', $fieldError);
            }

            if (!empty($documentTemplate->file_path)) {
                Storage::delete($documentTemplate->file_path);
            }
            $documentTemplate->file_path = $uploadedFile->store('document_templates');
            $documentTemplate->file_type = strtolower((string) $uploadedFile->getClientOriginalExtension());
        }

        if (!empty($validated['is_default'])) {
            DocumentTemplate::query()->where('type', $validated['type'])->where('id', '!=', $documentTemplate->id)->update(['is_default' => false]);
        }

        $documentTemplate->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'file_path' => $documentTemplate->file_path,
            'file_type' => $documentTemplate->file_type ?? 'docx',
        ]);

        return back()->with('success', 'Đã cập nhật mẫu in.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        if (!empty($documentTemplate->file_path)) {
            Storage::delete($documentTemplate->file_path);
        }

        $documentTemplate->delete();

        return back()->with('success', 'Đã xóa mẫu in.');
    }

    public function renderQuote(DocumentTemplate $documentTemplate, Quote $quote)
    {
        try {
            if (!in_array((string) $documentTemplate->type, ['quote', 'shared'], true)) {
                return back()->with('error', 'Mẫu này không áp dụng cho báo giá.');
            }

        $quote->load(['items.product.category']);
        $items = $quote->items ?? collect();

        $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
        $discountPercent = (float) ($quote->discount_percent ?? 0);
        $vatPercent = (float) ($quote->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $total = $afterDiscount + $vatAmount;

        $data = [
            'QuoteCode' => (string) ($quote->quote_code ?? ''),
            'CustomerName' => (string) ($quote->invoice_company_name ?: $quote->receiver_name),
            'TaxCode' => (string) ($quote->customer_tax_code ?? ''),
            'Address' => (string) (($quote->invoice_address ?: $quote->receiver_address) ?? ''),
            'InvoiceAddress' => (string) ($quote->invoice_address ?? ''),
            'ReceiverAddress' => (string) ($quote->receiver_address ?? ''),
            'ContactPerson' => (string) ($quote->customer_contact_person ?? ''),
            'Phone' => (string) ($quote->customer_phone ?? $quote->receiver_phone ?? ''),
            'Email' => (string) ($quote->customer_email ?? ''),
            'Date' => optional($quote->created_at)->format('d/m/Y') ?: '',
            'StaffCode' => (string) ($quote->staff_code ?? ''),
            'CreatedBy' => (string) ($quote->sales_name ?: optional($quote->user)->name ?: ''),
            'Warranty' => (string) ($quote->warranty_note ?? ''),
            'SubTotal' => number_format($subTotal, 0, ',', '.'),
            'VatPercent' => rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.'),
            'VatAmount' => number_format($vatAmount, 0, ',', '.'),
            'DiscountPercent' => rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.'),
            'TotalAmount' => number_format($total, 0, ',', '.'),
            'TotalAmountInWords' => $this->numberToVietnameseWords((int) round($total)),
        ];

        $itemRows = [];
        $firstCategory = '';
        $firstItemName = '';
        foreach ($items as $idx => $item) {
            $line = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
            $img = null;
            $imgName = (string) ($item->product->image ?? '');
            if ($imgName !== '') {
                $candidates = [
                    public_path('images/products/' . $imgName),
                    public_path($imgName),
                    storage_path('app/public/' . ltrim($imgName, '/')),
                ];
                foreach ($candidates as $c) {
                    if (is_file($c)) { $img = $c; break; }
                }
            }

            $cat = (string) (
                $item->product->category->name
                ?? $item->product->category_name
                ?? $item->category_name
                ?? 'Khác'
            );
            if ($firstCategory === '' && $cat !== '') {
                $firstCategory = $cat;
            }
            $name = (string) ($item->product->name ?? ('SP #' . $item->product_id));
            if ($firstItemName === '' && $name !== '') {
                $firstItemName = $name;
            }

            $itemRows[] = [
                'No' => $idx + 1,
                'Name' => $name,
                'Category' => $cat,
                'ItemCategory' => $cat,
                'Unit' => (string) ($item->unit ?? ''),
                'Quantity' => (string) ((int) ($item->quantity ?? 0)),
                'UnitPrice' => number_format((float) ($item->price ?? 0), 0, ',', '.'),
                'LineTotal' => number_format($line, 0, ',', '.'),
                'Image' => $img,
            ];
        }

        if (($data['CreatedBy'] ?? '') === '') {
            $data['CreatedBy'] = (string) (auth()->user()->name ?? '');
        }
        if (($data['StaffCode'] ?? '') === '') {
            $data['StaffCode'] = 'N/A';
        }
        if (!empty($itemRows)) {
            $firstItem = $itemRows[0];
            $data['Item.No'] = (string) ($firstItem['No'] ?? '');
            $data['Item.Name'] = (string) ($firstItem['Name'] ?? '');
            $data['Item.Category'] = (string) ($firstItem['Category'] ?? '');
            $data['ItemCategory'] = (string) ($firstItem['Category'] ?? '');
            $data['Category'] = (string) ($firstItem['Category'] ?? '');
            $data['Item.Unit'] = (string) ($firstItem['Unit'] ?? '');
            $data['Item.Quantity'] = (string) ($firstItem['Quantity'] ?? '');
            $data['Item.UnitPrice'] = (string) ($firstItem['UnitPrice'] ?? '');
            $data['Item.LineTotal'] = (string) ($firstItem['LineTotal'] ?? '');
        }

        if (($data['CreatedBy'] ?? '') === '') {
            $data['CreatedBy'] = (string) (auth()->user()->name ?? '');
        }
        if (($data['StaffCode'] ?? '') === '') {
            $data['StaffCode'] = 'N/A';
        }
        if (!empty($itemRows)) {
            $firstItem = $itemRows[0];
            $data['Item.No'] = (string) ($firstItem['No'] ?? '');
            $data['Item.Name'] = (string) ($firstItem['Name'] ?? '');
            $data['Item.Category'] = (string) ($firstItem['Category'] ?? '');
            $data['ItemCategory'] = (string) ($firstItem['Category'] ?? '');
            $data['Category'] = (string) ($firstItem['Category'] ?? '');
            $data['Item.Unit'] = (string) ($firstItem['Unit'] ?? '');
            $data['Item.Quantity'] = (string) ($firstItem['Quantity'] ?? '');
            $data['Item.UnitPrice'] = (string) ($firstItem['UnitPrice'] ?? '');
            $data['Item.LineTotal'] = (string) ($firstItem['LineTotal'] ?? '');
        }

        $templateAbsPath = Storage::path((string) $documentTemplate->file_path);
        if (!is_file($templateAbsPath)) {
            return back()->with('error', 'Không tìm thấy file template.');
        }

            $ext = strtolower((string) pathinfo($templateAbsPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $filled = $this->renderSpreadsheetTemplateToTempFile($templateAbsPath, $data, $itemRows, $ext);
                $downloadName = 'bao-gia-' . ($quote->quote_code ?: $quote->id) . '.xlsx';
                return Response::download($filled, $downloadName)->deleteFileAfterSend(true);
            }

            $filled = DocxTemplateRenderer::renderToTempFile($templateAbsPath, $data, $itemRows);
            $downloadName = 'bao-gia-' . ($quote->quote_code ?: $quote->id) . '.docx';
            return Response::download($filled, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('render_quote_template_failed', [
                'template_id' => $documentTemplate->id ?? null,
                'quote_id' => $quote->id ?? null,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'In theo mẫu thất bại: ' . $e->getMessage());
        }
    }

    public function renderSalesOrder(DocumentTemplate $documentTemplate, SalesOrder $salesOrder)
    {
        try {
            if (!in_array((string) $documentTemplate->type, ['sales_order', 'quote', 'shared'], true)) {
                return back()->with('error', 'Mẫu này không áp dụng cho đơn hàng.');
            }

        $salesOrder->load(['items.product.category', 'quote']);
        $items = $salesOrder->items ?? collect();

        $subTotal = (float) $items->sum(fn($i) => (float) ($i->unit_price ?? 0) * (int) ($i->quantity ?? 0));
        $discountPercent = (float) ($salesOrder->discount_percent ?? 0);
        $vatPercent = (float) ($salesOrder->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $total = $afterDiscount + $vatAmount;

        $data = [
            'SalesOrderCode' => (string) ($salesOrder->sales_order_code ?? ''),
            'QuoteCode' => (string) optional($salesOrder->quote)->quote_code,
            'CustomerName' => (string) ($salesOrder->invoice_company_name ?: $salesOrder->receiver_name),
            'TaxCode' => (string) ($salesOrder->customer_tax_code ?? ''),
            'Address' => (string) (($salesOrder->invoice_address ?: $salesOrder->receiver_address) ?? ''),
            'InvoiceAddress' => (string) ($salesOrder->invoice_address ?? ''),
            'ReceiverAddress' => (string) ($salesOrder->receiver_address ?? ''),
            'ContactPerson' => (string) ($salesOrder->customer_contact_person ?? ''),
            'Phone' => (string) ($salesOrder->customer_phone ?? $salesOrder->receiver_phone ?? ''),
            'Email' => (string) ($salesOrder->customer_email ?? ''),
            'Date' => optional($salesOrder->created_at)->format('d/m/Y') ?: '',
            'StaffCode' => (string) ($salesOrder->staff_code ?? ''),
            'CreatedBy' => (string) ($salesOrder->sales_name ?? ''),
            'Warranty' => (string) ($salesOrder->warranty_note ?? optional($salesOrder->quote)->warranty_note ?? ''),
            'SubTotal' => number_format($subTotal, 0, ',', '.'),
            'VatPercent' => rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.'),
            'VatAmount' => number_format($vatAmount, 0, ',', '.'),
            'DiscountPercent' => rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.'),
            'TotalAmount' => number_format($total, 0, ',', '.'),
            'TotalAmountInWords' => $this->numberToVietnameseWords((int) round($total)),
        ];

        $itemRows = [];
        $firstCategory = '';
        foreach ($items as $idx => $item) {
            $line = (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
            $img = null;
            $imgName = (string) ($item->product->image ?? '');
            if ($imgName !== '') {
                $candidates = [
                    public_path('images/products/' . $imgName),
                    public_path($imgName),
                    storage_path('app/public/' . ltrim($imgName, '/')),
                ];
                foreach ($candidates as $c) {
                    if (is_file($c)) { $img = $c; break; }
                }
            }

            $cat = (string) (
                $item->product->category->name
                ?? $item->product->category_name
                ?? $item->category_name
                ?? 'Khác'
            );
            if ($firstCategory === '' && $cat !== '') {
                $firstCategory = $cat;
            }
            $itemRows[] = [
                'No' => $idx + 1,
                'Name' => (string) ($item->product->name ?? ('SP #' . $item->product_id)),
                'Category' => $cat,
                'ItemCategory' => $cat,
                'Unit' => (string) ($item->unit ?? ''),
                'Quantity' => (string) ((int) ($item->quantity ?? 0)),
                'UnitPrice' => number_format((float) ($item->unit_price ?? 0), 0, ',', '.'),
                'LineTotal' => number_format($line, 0, ',', '.'),
                'Image' => $img,
            ];
        }

        $templateAbsPath = Storage::path((string) $documentTemplate->file_path);
        if (!is_file($templateAbsPath)) {
            return back()->with('error', 'Không tìm thấy file template.');
        }

            $ext = strtolower((string) pathinfo($templateAbsPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $filled = $this->renderSpreadsheetTemplateToTempFile($templateAbsPath, $data, $itemRows, $ext);
                $pdfPath = $this->convertOfficeFileToPdf($filled);
                if ($pdfPath) {
                    $downloadName = 'don-hang-' . ($salesOrder->sales_order_code ?: $salesOrder->id) . '.pdf';
                    return Response::download($pdfPath, $downloadName)->deleteFileAfterSend(true);
                }
                $downloadName = 'don-hang-' . ($salesOrder->sales_order_code ?: $salesOrder->id) . '.xlsx';
                return Response::download($filled, $downloadName)->deleteFileAfterSend(true);
            }

            $filled = DocxTemplateRenderer::renderToTempFile($templateAbsPath, $data, $itemRows);
            $pdfPath = $this->convertOfficeFileToPdf($filled);
            if ($pdfPath) {
                $downloadName = 'don-hang-' . ($salesOrder->sales_order_code ?: $salesOrder->id) . '.pdf';
                return Response::download($pdfPath, $downloadName)->deleteFileAfterSend(true);
            }

            $downloadName = 'don-hang-' . ($salesOrder->sales_order_code ?: $salesOrder->id) . '.docx';
            return Response::download($filled, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('render_sales_order_template_failed', [
                'template_id' => $documentTemplate->id ?? null,
                'sales_order_id' => $salesOrder->id ?? null,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'In theo mẫu thất bại: ' . $e->getMessage());
        }
    }

    public function renderDefaultQuote(Quote $quote)
    {
        $template = DocumentTemplate::query()
            ->where('type', 'quote')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();

        if (!$template) {
            return back()->with('error', 'Chưa có mẫu báo giá đang kích hoạt.');
        }

        return $this->renderQuote($template, $quote);
    }

    public function renderDefaultSalesOrder(SalesOrder $salesOrder)
    {
        $template = DocumentTemplate::query()
            ->whereIn('type', ['sales_order', 'quote', 'shared'])
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();

        if (!$template) {
            return back()->with('error', 'Chưa có mẫu đơn hàng đang kích hoạt.');
        }

        return $this->renderSalesOrder($template, $salesOrder);
    }

    public function renderPdf(DocumentTemplate $documentTemplate, Quote $quote)
    {
        try {
            if (!in_array((string) $documentTemplate->type, ['pdf', 'quote', 'shared'], true)) {
                return back()->with('error', 'Mẫu này không áp dụng cho PDF.');
            }

            $quote->load(['items.product.category']);
            $items = $quote->items ?? collect();
            $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
            $discountPercent = (float) ($quote->discount_percent ?? 0);
            $vatPercent = (float) ($quote->vat_percent ?? 8);
            $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
            $vatAmount = $afterDiscount * ($vatPercent / 100);
            $total = $afterDiscount + $vatAmount;

            $itemRows = [];
            foreach ($items as $idx => $item) {
                $itemRows[] = [
                    'No' => $idx + 1,
                    'Name' => (string) ($item->product->name ?? ''),
                    'Quantity' => (string) ((int) ($item->quantity ?? 0)),
                    'UnitPrice' => number_format((float) ($item->price ?? 0), 0, ',', '.'),
                    'LineTotal' => number_format((float) ($item->price ?? 0) * (int) ($item->quantity ?? 0), 0, ',', '.'),
                ];
            }

            $html = $this->buildPdfHtml($quote, [
                'QuoteCode' => (string) ($quote->quote_code ?? ''),
                'CustomerName' => (string) ($quote->invoice_company_name ?: $quote->receiver_name),
                'Date' => optional($quote->created_at)->format('d/m/Y') ?: '',
                'SubTotal' => number_format($subTotal, 0, ',', '.'),
                'VatPercent' => rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.'),
                'VatAmount' => number_format($vatAmount, 0, ',', '.'),
                'DiscountPercent' => rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.'),
                'TotalAmount' => number_format($total, 0, ',', '.'),
                'TotalAmountInWords' => $this->numberToVietnameseWords((int) round($total)),
            ], $itemRows);

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            $downloadName = 'bao-gia-' . ($quote->quote_code ?: $quote->id) . '.pdf';
            return $pdf->download($downloadName);
        } catch (\Throwable $e) {
            Log::error('render_pdf_template_failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Xuất PDF thất bại: ' . $e->getMessage());
        }
    }

    private function buildPdfHtml(Quote $quote, array $data, array $items): string
    {
        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr><td>' . e($item['No'] ?? '') . '</td><td>' . e($item['Name'] ?? '') . '</td><td>' . e($item['Quantity'] ?? '') . '</td><td>' . e($item['UnitPrice'] ?? '') . '</td><td>' . e($item['LineTotal'] ?? '') . '</td></tr>';
        }

        return '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:12px}.title{text-align:center;font-size:18px;font-weight:bold}.meta{margin:12px 0}table{width:100%;border-collapse:collapse}th,td{border:1px solid #333;padding:6px}</style></head><body><div class="title">BÁO GIÁ</div><div class="meta">Mã: ' . e($data['QuoteCode'] ?? '') . '<br>Khách hàng: ' . e($data['CustomerName'] ?? '') . '<br>Ngày: ' . e($data['Date'] ?? '') . '</div><table><thead><tr><th>#</th><th>Tên</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>' . $rows . '</tbody></table><div style="margin-top:12px">Tổng: ' . e($data['TotalAmount'] ?? '') . '</div></body></html>';
    }

    private function renderSpreadsheetTemplateToTempFile(string $templatePath, array $data, array $items, string $ext = 'xlsx'): string
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // 1) Fill field thường
            for ($row = 1; $row <= $highestRow; $row++) {
                $range = 'A' . $row . ':' . $highestColumn . $row;
                $cells = $sheet->rangeToArray($range, null, true, true, true);
                foreach (($cells[$row] ?? []) as $col => $value) {
                    if (!is_string($value) || $value === '') {
                        continue;
                    }

                    $new = $value;
                    foreach ($data as $k => $v) {
                        $safe = (string) ($v ?? '');
                        $p1 = '/\{\{\s*' . preg_quote((string) $k, '/') . '\s*\}\}/u';
                        $p2 = '/\{\{\{\s*' . preg_quote((string) $k, '/') . '\s*\}\}\}/u';
                        $new = preg_replace($p1, $safe, $new) ?: $new;
                        $new = preg_replace($p2, $safe, $new) ?: $new;
                    }

                    if ($new !== $value) {
                        $sheet->setCellValue($col . $row, $new);
                    }
                }
            }

            // 2) Fill block Items: {{#Items}} ... {{/Items}}
            $highestRow = $sheet->getHighestRow();
            $startRow = null;
            $endRow = null;

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowText = '';
                $range = 'A' . $row . ':' . $highestColumn . $row;
                $cells = $sheet->rangeToArray($range, null, true, true, true);
                foreach (($cells[$row] ?? []) as $value) {
                    if (is_string($value)) {
                        $rowText .= ' ' . $value;
                    }
                }
                if ($startRow === null && str_contains($rowText, '{{#Items}}')) {
                    $startRow = $row;
                }
                if ($endRow === null && str_contains($rowText, '{{/Items}}')) {
                    $endRow = $row;
                }
            }

            if ($startRow !== null && $endRow !== null && $endRow >= $startRow) {
                $tplStart = $startRow;
                $tplEnd = $endRow;
                $tplRowCount = $tplEnd - $tplStart + 1;

                // nếu có item thì chèn thêm số dòng cần thiết
                $itemCount = count($items);
                if ($itemCount > 1) {
                    $sheet->insertNewRowBefore($tplEnd + 1, ($itemCount - 1) * $tplRowCount);
                }

                for ($i = 0; $i < max(1, $itemCount); $i++) {
                    $item = $items[$i] ?? [];
                    for ($r = 0; $r < $tplRowCount; $r++) {
                        $rowIdx = $tplStart + ($i * $tplRowCount) + $r;
                        $range = 'A' . $rowIdx . ':' . $highestColumn . $rowIdx;
                        $cells = $sheet->rangeToArray($range, null, true, true, true);

                        foreach (($cells[$rowIdx] ?? []) as $col => $value) {
                            if (!is_string($value) || $value === '') {
                                continue;
                            }

                            $new = str_replace(['{{#Items}}', '{{/Items}}'], '', $value);
                            foreach ($item as $k => $v) {
                                if ($k === 'Image') {
                                    continue;
                                }
                                $safe = (string) ($v ?? '');
                                $p1 = '/\{\{\s*Item\.' . preg_quote((string) $k, '/') . '\s*\}\}/u';
                                $p2 = '/\{\{\{\s*Item\.' . preg_quote((string) $k, '/') . '\s*\}\}\}/u';
                                $new = preg_replace($p1, $safe, $new) ?: $new;
                                $new = preg_replace($p2, $safe, $new) ?: $new;
                            }

                            // chèn ảnh nếu có {{Item.Image}}
                            if (str_contains($new, '{{Item.Image}}')) {
                                $new = str_replace('{{Item.Image}}', '', $new);
                                $imgPath = (string) ($item['Image'] ?? '');
                                if ($imgPath !== '' && is_file($imgPath)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setPath($imgPath);
                                    $drawing->setCoordinates($col . $rowIdx);
                                    $drawing->setHeight(48);
                                    $drawing->setWorksheet($sheet);
                                }
                            }

                            // dọn token item chưa map
                            $new = preg_replace('/\{\{\s*Item\.[^\}]+\}\}/', '', (string) $new) ?: $new;

                            if ($new !== $value) {
                                $sheet->setCellValue($col . $rowIdx, $new);
                            }
                        }
                    }
                }
            }
        }

        $outExt = in_array(strtolower($ext), ['xls', 'xlsx'], true) ? strtolower($ext) : 'xlsx';
        $outPath = storage_path('app/tmp/template-' . uniqid('', true) . '.' . $outExt);
        if (!is_dir(dirname($outPath))) {
            @mkdir(dirname($outPath), 0777, true);
        }

        $writerType = $outExt === 'xls' ? 'Xls' : 'Xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $writerType);
        $writer->save($outPath);

        return $outPath;
    }

    private function convertOfficeFileToPdf(string $inputPath): ?string
    {
        try {
            $office = trim((string) shell_exec('where soffice'));
            if ($office === '') {
                $office = trim((string) shell_exec('where libreoffice'));
            }
            if ($office === '') {
                return null;
            }

            $inputPath = str_replace('\\', '/', $inputPath);
            $outDir = str_replace('\\', '/', dirname($inputPath));

            $cmd = '"' . $office . '" --headless --convert-to pdf --outdir "' . $outDir . '" "' . $inputPath . '"';
            @exec($cmd, $output, $code);

            $pdfPath = preg_replace('/\.[^.]+$/', '.pdf', $inputPath);
            if ($code === 0 && is_file($pdfPath)) {
                return $pdfPath;
            }

            return null;
        } catch (\Throwable $e) {
            Log::warning('convert_office_pdf_failed', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function numberToVietnameseWords(int $number): string
    {
        if ($number <= 0) {
            return 'Không đồng';
        }

        $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

        $read3 = function (int $n, bool $full = false) use ($digits): string {
            $hund = intdiv($n, 100);
            $tens = intdiv($n % 100, 10);
            $ones = $n % 10;
            $parts = [];

            if ($full || $hund > 0) {
                $parts[] = $digits[$hund] . ' trăm';
                if ($tens === 0 && $ones > 0) {
                    $parts[] = 'lẻ';
                }
            }

            if ($tens > 1) {
                $parts[] = $digits[$tens] . ' mươi';
                if ($ones === 1) {
                    $parts[] = 'mốt';
                } elseif ($ones === 4) {
                    $parts[] = 'tư';
                } elseif ($ones === 5) {
                    $parts[] = 'lăm';
                } elseif ($ones > 0) {
                    $parts[] = $digits[$ones];
                }
            } elseif ($tens === 1) {
                $parts[] = 'mười';
                if ($ones === 5) {
                    $parts[] = 'lăm';
                } elseif ($ones > 0) {
                    $parts[] = $digits[$ones];
                }
            } elseif ($ones > 0 && !$full) {
                $parts[] = $digits[$ones];
            } elseif ($ones > 0) {
                $parts[] = $digits[$ones];
            }

            return trim(implode(' ', $parts));
        };

        $chunks = [];
        while ($number > 0) {
            $chunks[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $parts = [];
        $hasPrev = false;
        for ($i = count($chunks) - 1; $i >= 0; $i--) {
            $chunk = $chunks[$i];
            if ($chunk === 0) {
                if ($hasPrev) {
                    $hasPrev = true;
                }
                continue;
            }
            $full = $hasPrev && $chunk < 100;
            $txt = $read3($chunk, $full);
            $parts[] = trim($txt . ' ' . ($units[$i] ?? ''));
            $hasPrev = true;
        }

        $result = trim(implode(' ', $parts));
        $result = mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);

        return $result . ' đồng';
    }

    private function validateTemplateFields(string $filePath, string $ext = 'docx'): ?string
    {
        try {
            $ext = strtolower(trim($ext));
            $rawText = '';

            if ($ext === 'docx') {
                $zip = new \ZipArchive();
                if ($zip->open($filePath) !== true) {
                    return 'Không mở được file DOCX để kiểm tra field.';
                }
                $xml = $zip->getFromName('word/document.xml');
                $zip->close();

                if ($xml === false) {
                    return 'File DOCX không hợp lệ (thiếu document.xml).';
                }
                $rawText = (string) $xml;
            } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();
                    for ($row = 1; $row <= $highestRow; $row++) {
                        $range = 'A' . $row . ':' . $highestColumn . $row;
                        $cells = $sheet->rangeToArray($range, null, true, true, true);
                        foreach (($cells[$row] ?? []) as $value) {
                            if (is_string($value) && $value !== '') {
                                $rawText .= "\n" . $value;
                            }
                        }
                    }
                }
            } else {
                return 'Định dạng template chưa hỗ trợ. Chỉ nhận DOCX/XLSX/XLS.';
            }

            preg_match_all('/\{\{\s*([^\}]+?)\s*\}\}/', $rawText, $m);
            $found = collect($m[1] ?? [])->map(fn($x) => trim((string) $x))->filter()->unique()->values();
            if ($found->isEmpty()) {
                return null;
            }

            $systemFields = collect(self::SUPPORTED_FIELDS)
                ->reject(fn($x) => str_starts_with((string) $x, 'Item.') || in_array($x, ['#Items', '/Items'], true))
                ->values();
            $itemFields = collect(self::SUPPORTED_FIELDS)
                ->filter(fn($x) => str_starts_with((string) $x, 'Item.') || in_array($x, ['#Items', '/Items'], true))
                ->values();

            $unknown = $found->filter(fn($f) => !in_array($f, self::SUPPORTED_FIELDS, true))->values();

            $hasOpenItems = str_contains($rawText, '{{#Items}}');
            $hasCloseItems = str_contains($rawText, '{{/Items}}');
            if ($hasOpenItems xor $hasCloseItems) {
                return 'Template thiếu cặp block Items đầy đủ. Cần có cả {{#Items}} và {{/Items}}.';
            }

            if ($unknown->isNotEmpty()) {
                $lines = [];
                foreach ($unknown as $bad) {
                    $best = collect(self::SUPPORTED_FIELDS)
                        ->map(fn($ok) => ['field' => $ok, 'dist' => levenshtein(Str::lower((string) $bad), Str::lower((string) $ok))])
                        ->sortBy('dist')
                        ->first();
                    $suggest = ($best && ($best['dist'] ?? 999) <= 4) ? (' -> gợi ý: {{' . $best['field'] . '}}') : '';
                    $lines[] = '{{' . $bad . '}}' . $suggest;
                }

                return "Template có field chưa hỗ trợ/sai chính tả:\n- " . implode("\n- ", $lines)
                    . "\n\nField hệ thống hợp lệ: " . $systemFields->map(fn($f) => '{{' . $f . '}}')->implode(', ')
                    . "\nField item hợp lệ: " . $itemFields->map(fn($f) => '{{' . $f . '}}')->implode(', ');
            }

            return null;
        } catch (\Throwable $e) {
            return 'Không thể kiểm tra field template: ' . $e->getMessage();
        }
    }

    public function downloadFields(Request $request)
    {
        $rows = collect([
            // Hướng dẫn chung
            ['field' => '{{QuoteCode}}', 'description' => 'Mã báo giá'],
            ['field' => '{{SalesOrderCode}}', 'description' => 'Số đơn hàng'],
            ['field' => '{{Date}}', 'description' => 'Ngày chứng từ (d/m/Y)'],
            ['field' => '{{StaffCode}}', 'description' => 'Mã nhân viên (staff code)'],
            ['field' => '{{CreatedBy}}', 'description' => 'Báo giá/đơn hàng được lập bởi'],
            ['field' => '{{Warranty}}', 'description' => 'Thông tin bảo hành'],

            // Khách hàng
            ['field' => '{{CustomerName}}', 'description' => 'Tên công ty/khách hàng'],
            ['field' => '{{TaxCode}}', 'description' => 'Mã số thuế'],
            ['field' => '{{Address}}', 'description' => 'Địa chỉ mặc định (ưu tiên địa chỉ xuất hóa đơn, fallback địa chỉ nhận hàng)'],
            ['field' => '{{InvoiceAddress}}', 'description' => 'Địa chỉ xuất hóa đơn'],
            ['field' => '{{ReceiverAddress}}', 'description' => 'Địa chỉ nhận hàng'],
            ['field' => '{{ContactPerson}}', 'description' => 'Người liên hệ'],
            ['field' => '{{Phone}}', 'description' => 'Số điện thoại'],
            ['field' => '{{Email}}', 'description' => 'Email'],

            // Tiền tệ
            ['field' => '{{SubTotal}}', 'description' => 'Tạm tính'],
            ['field' => '{{DiscountPercent}}', 'description' => 'Chiết khấu (%)'],
            ['field' => '{{VatPercent}}', 'description' => 'VAT (%) - ví dụ: 8'],
            ['field' => '{{VatAmount}}', 'description' => 'Tiền VAT (số tiền)'],
            ['field' => '{{TotalAmount}}', 'description' => 'Tổng cộng'],
            ['field' => '{{TotalAmountInWords}}', 'description' => 'Tổng tiền bằng chữ'],

            // Block items
            ['field' => '{{#Items}}', 'description' => 'Bắt đầu block lặp dòng hàng'],
            ['field' => '{{Item.No}}', 'description' => 'STT dòng hàng'],
            ['field' => '{{Item.Name}}', 'description' => 'Tên sản phẩm/dịch vụ'],
            ['field' => '{{Item.Category}}', 'description' => 'Tên danh mục của sản phẩm'],
            ['field' => '{{Item.Unit}}', 'description' => 'Đơn vị tính'],
            ['field' => '{{Item.Quantity}}', 'description' => 'Số lượng'],
            ['field' => '{{Item.UnitPrice}}', 'description' => 'Đơn giá'],
            ['field' => '{{Item.LineTotal}}', 'description' => 'Thành tiền dòng'],
            ['field' => '{{Item.Image}}', 'description' => 'Ảnh sản phẩm (Excel: chèn ảnh vào cell chứa placeholder)'],
            ['field' => '{{/Items}}', 'description' => 'Kết thúc block lặp dòng hàng'],

            // Gợi ý dùng trực tiếp
            ['field' => 'Ví dụ dòng item', 'description' => '{{#Items}} {{Item.No}} | {{Item.Name}} | {{Item.Quantity}} | {{Item.UnitPrice}} | {{Item.LineTotal}} {{/Items}}'],
        ]);

        $filename = 'truong-tron-mau-in-' . now()->format('YmdHis') . '.xlsx';

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private $rows) {}
            public function collection() { return $this->rows; }
            public function headings(): array { return ['field', 'description']; }
        }, $filename);
    }
}
