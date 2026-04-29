<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdfTemplate;
use App\Models\Quote;
use App\Support\LineVatCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfTemplateController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type', 'quote'));
        $query = PdfTemplate::query()->orderByDesc('is_default')->orderByDesc('created_at');

        if ($type !== '') {
            $query->where('type', $type);
        }

        $templates = $query->paginate(20)->withQueryString();
        $activeTemplate = PdfTemplate::query()
            ->where('type', $type ?: 'quote')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();
        $sampleQuote = Quote::query()->latest('id')->with(['items.product.category'])->first();
        $previewData = $sampleQuote ? $this->buildQuoteData($sampleQuote) : null;
        $availableViews = [
            'preview' => 'preview.blade.php',
            '2preview' => '2preview.blade.php',
        ];

        return view('admin.pdf_templates.index', compact('templates', 'type', 'activeTemplate', 'sampleQuote', 'previewData', 'availableViews'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quote,sales_order,invoice'],
            'view_name' => ['required', 'in:preview,2preview'],
            'html_content' => ['nullable', 'string'],
            'css_content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $defaultHtml = '<div class="page"><div style="font-family: DejaVu Sans, sans-serif;">Mẫu PDF mới</div></div>';
        $defaultCss = '@page { size: A4; margin: 12mm; }';

        if (!empty($validated['is_default'])) {
            PdfTemplate::query()->where('type', $validated['type'])->update(['is_default' => false]);
        }

        PdfTemplate::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'view_name' => $validated['view_name'] ?? 'preview',
            'html_content' => trim((string) ($validated['html_content'] ?? '')) ?: $defaultHtml,
            'css_content' => trim((string) ($validated['css_content'] ?? '')) ?: $defaultCss,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        return back()->with('success', 'Đã tạo mẫu PDF.');
    }

    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:quote,sales_order,invoice'],
            'view_name' => ['required', 'in:preview,2preview'],
            'html_content' => ['required', 'string'],
            'css_content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_default'])) {
            PdfTemplate::query()
                ->where('type', $validated['type'])
                ->where('id', '!=', $pdfTemplate->id)
                ->update(['is_default' => false]);
        }

        $pdfTemplate->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'view_name' => $validated['view_name'],
            'html_content' => $validated['html_content'],
            'css_content' => $validated['css_content'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_default' => (bool) ($validated['is_default'] ?? false),
        ]);

        return back()->with('success', 'Đã cập nhật mẫu PDF.');
    }

    public function destroy(PdfTemplate $pdfTemplate)
    {
        $pdfTemplate->delete();
        return back()->with('success', 'Đã xóa mẫu PDF.');
    }

    public function clone(PdfTemplate $pdfTemplate)
    {
        $clone = $pdfTemplate->replicate();
        $clone->name = $pdfTemplate->name . ' (bản sao)';
        $clone->is_active = false;
        $clone->is_default = false;
        $clone->save();

        return back()->with('success', 'Đã tạo bản sao mẫu PDF để chỉnh sửa.');
    }

    public function toggleActive(PdfTemplate $pdfTemplate)
    {
        $pdfTemplate->update(['is_active' => ! $pdfTemplate->is_active]);
        return back()->with('success', $pdfTemplate->is_active ? 'Đã kích hoạt mẫu PDF.' : 'Đã hủy kích hoạt mẫu PDF.');
    }

    public function setDefault(PdfTemplate $pdfTemplate)
    {
        PdfTemplate::query()->where('type', $pdfTemplate->type)->update(['is_default' => false]);
        $pdfTemplate->update(['is_default' => true, 'is_active' => true]);

        return back()->with('success', 'Đã đặt làm mẫu mặc định.');
    }

    public function preview(PdfTemplate $pdfTemplate)
    {
        try {
            $sampleQuote = Quote::query()->latest('id')->with(['items.product.category'])->first();
            if (!$sampleQuote) {
                return back()->with('error', 'Chưa có dữ liệu báo giá để xem trước.');
            }

            $data = $this->buildQuoteData($sampleQuote);
            $templateView = $pdfTemplate->view_name ?: 'preview';

            $html = view('admin.pdf_templates.' . $templateView, [
                'quote' => $sampleQuote,
                'customerName' => $data['CustomerName'],
                'address' => $data['Address'],
                'taxCode' => $data['TaxCode'],
                'phone' => $data['Phone'],
                'email' => $data['Email'],
                'paymentTermLabel' => $data['PaymentTermLabel'],
                'paymentDueDays' => $data['PaymentDueDays'],
                'depositPercent' => $data['DepositPercent'],
                'paymentNote' => $data['PaymentNote'],
                'itemRows' => $data['ItemRows'],
                'subTotal' => $data['SubTotal'],
                'vatAmount' => $data['VatAmount'],
                'total' => $data['TotalAmount'],
                'totalInWords' => $data['TotalAmountInWords'],
            ])->render();

            return Pdf::loadHTML($html)->setPaper('a4', 'portrait')->stream('preview.pdf');
        } catch (\Throwable $e) {
            \Log::error('pdf_template_preview_failed', [
                'template_id' => $pdfTemplate->id ?? null,
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Xem trước PDF thất bại: ' . $e->getMessage());
        }
    }

    public function render(PdfTemplate $pdfTemplate, Quote $quote)
    {
        if (!in_array((string) $pdfTemplate->type, ['quote', 'invoice'], true)) {
            return back()->with('error', 'Mẫu PDF này chưa hỗ trợ loại chứng từ này.');
        }

        $quote->load(['items.product.category']);
        $data = $this->buildQuoteData($quote);

        $viewName = $pdfTemplate->view_name ?: 'preview';
        $html = view('admin.pdf_templates.' . $viewName, [
            'quote' => $quote,
            'customerName' => $data['CustomerName'],
            'address' => $data['Address'],
            'taxCode' => $data['TaxCode'],
            'phone' => $data['Phone'],
            'email' => $data['Email'],
            'paymentTermLabel' => $data['PaymentTermLabel'],
            'paymentDueDays' => $data['PaymentDueDays'],
            'depositPercent' => $data['DepositPercent'],
            'paymentNote' => $data['PaymentNote'],
            'itemRows' => $data['ItemRows'],
            'subTotal' => $data['SubTotal'],
            'vatAmount' => $data['VatAmount'],
            'total' => $data['TotalAmount'],
            'totalInWords' => $data['TotalAmountInWords'],
        ])->render();

        return Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->download('bao-gia-' . ($quote->quote_code ?: $quote->id) . '.pdf');
    }

    public function renderDefaultQuote(Quote $quote)
    {
        $template = PdfTemplate::query()
            ->where('type', 'quote')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();

        if (!$template) {
            return back()->with('error', 'Chưa có mẫu PDF báo giá đang kích hoạt.');
        }

        return $this->render($template, $quote);
    }

    private function buildQuoteData(Quote $quote): array
    {
        $items = $quote->items ?? collect();
        $discountPercent = (float) ($quote->discount_percent ?? 0);
        $totals = LineVatCalculator::totals($items, 'price', $discountPercent, (float) ($quote->vat_percent ?? 8));
        $subTotal = (float) $totals['sub_total'];
        $vatAmount = (float) $totals['vat_amount'];
        $total = (float) $totals['total'];

        $itemRows = '';
        foreach ($items as $idx => $item) {
            $line = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
            $lineVatPercent = (float) ($item->vat_percent ?? $quote->vat_percent ?? 8);
            $lineVatAmount = $line * ($lineVatPercent / 100);
            $lineAfterVat = $line + $lineVatAmount;
            $img = (string) ($item->product->image ?? '');
            $imgPath = $img !== '' ? public_path('images/products/' . ltrim($img, '/')) : '';

            $productName = (string) ($item->product->name ?? ('SP #' . $item->product_id));
            $productInfo = trim((string) (
                $item->product->information
                ?? $item->product->description
                ?? ''
            ));
            if ($productInfo !== '') {
                $productInfo = strip_tags($productInfo);
            }

            $nameCell = '<b>' . e($productName) . '</b>';
            if ($productInfo !== '') {
                $nameCell .= '<div style="margin-top:3px; font-weight:400; font-size:10px; line-height:1.35;">' . e($productInfo) . '</div>';
            }

            $itemRows .= '<tr>'
                . '<td class="t-center">' . ($idx + 1) . '</td>'
                . '<td>' . $nameCell . '</td>'
                . '<td class="t-center">' . e((string) ($item->quantity ?? 0)) . '</td>'
                . '<td class="t-center">' . ($imgPath !== '' && file_exists($imgPath) ? '<img src="' . e($imgPath) . '" alt="" style="max-width:60px; max-height:60px; object-fit:contain;">' : '') . '</td>'
                . '<td class="t-right">' . number_format((float) ($item->price ?? 0), 0, ',', '.') . '</td>'
                . '<td class="t-right">' . number_format($lineAfterVat, 0, ',', '.') . '</td>'
                . '</tr>';
        }

        $paymentTerm = (string) ($quote->payment_term ?? 'full_advance');
        $paymentTermLabel = match ($paymentTerm) {
            'debt' => 'Công nợ theo hạn',
            'deposit' => 'Đặt cọc + phần còn lại',
            default => 'Thanh toán 100% trước giao hàng',
        };

        $paymentNote = (string) ($quote->payment_note ?? '');
        if ($paymentTerm === 'deposit' && $paymentNote === '') {
            $depositPercent = rtrim(rtrim(number_format((float) ($quote->deposit_percent ?? 0), 2, '.', ''), '0'), '.');
            $paymentNote = $depositPercent !== '' ? ($depositPercent . '% còn lại khi xong thanh toán') : 'Thanh toán phần còn lại khi xong thanh toán';
        }
        if ($paymentTerm === 'debt' && $paymentNote === '') {
            $days = (int) ($quote->payment_due_days ?? 0);
            $paymentNote = $days > 0 ? ('Công nợ trong ' . $days . ' ngày') : 'Công nợ theo thỏa thuận';
        }

        return [
            'QuoteCode' => (string) ($quote->quote_code ?? ''),
            'CustomerName' => (string) ($quote->invoice_company_name ?: $quote->receiver_name),
            'TaxCode' => (string) ($quote->customer_tax_code ?? ''),
            'Address' => (string) (($quote->invoice_address ?: $quote->receiver_address) ?? ''),
            'Date' => optional($quote->created_at)->format('d/m/Y') ?: '',
            'ContactPerson' => (string) ($quote->customer_contact_person ?? ''),
            'Phone' => (string) ($quote->customer_phone ?? $quote->receiver_phone ?? ''),
            'Email' => (string) ($quote->customer_email ?? ''),
            'PaymentTermLabel' => $paymentTermLabel,
            'PaymentDueDays' => (string) ((int) ($quote->payment_due_days ?? 0)),
            'DepositPercent' => rtrim(rtrim(number_format((float) ($quote->deposit_percent ?? 0), 2, '.', ''), '0'), '.'),
            'PaymentNote' => $paymentNote,
            'SubTotal' => number_format($subTotal, 0, ',', '.'),
            'VatPercent' => 'theo từng dòng',
            'VatAmount' => number_format($vatAmount, 0, ',', '.'),
            'DiscountPercent' => rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.'),
            'TotalAmount' => number_format($total, 0, ',', '.'),
            'TotalAmountInWords' => $this->numberToVietnameseWords((int) round($total)),
            'ItemRows' => $itemRows,
        ];
    }

    private function numberToVietnameseWords(int $number): string
    {
        if ($number === 0) {
            return 'Không đồng';
        }

        $scales = ['', 'nghìn', 'triệu', 'tỷ'];
        $groups = [];
        $scaleIndex = 0;

        while ($number > 0) {
            $groups[] = [
                'value' => $number % 1000,
                'scale' => $scales[$scaleIndex] ?? '',
            ];
            $number = intdiv($number, 1000);
            $scaleIndex++;
        }

        $groups = array_reverse($groups);
        $parts = [];
        $totalGroups = count($groups);

        foreach ($groups as $index => $group) {
            $value = (int) $group['value'];
            if ($value === 0) {
                continue;
            }

            $isHighestGroup = $index === 0;
            $words = $this->readVietnameseTriplet($value, $isHighestGroup);
            if ($words !== '') {
                $parts[] = trim($words . ' ' . $group['scale']);
            }
        }

        return ucfirst(trim(preg_replace('/\s+/', ' ', implode(' ', $parts)))) . ' đồng';
    }

    private function readVietnameseTriplet(int $number, bool $isHighestGroup = false): string
    {
        $digits = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $hundreds = intdiv($number, 100);
        $tens = intdiv($number % 100, 10);
        $ones = $number % 10;
        $words = [];

        if ($hundreds > 0) {
            $words[] = $digits[$hundreds] . ' trăm';
        } elseif (!$isHighestGroup && ($tens > 0 || $ones > 0)) {
            $words[] = 'không trăm';
        }

        if ($tens > 1) {
            $words[] = $digits[$tens] . ' mươi';
            if ($ones > 0) {
                $words[] = $ones === 1 ? 'mốt' : ($ones === 5 ? 'lăm' : $digits[$ones]);
            }
        } elseif ($tens === 1) {
            $words[] = 'mười';
            if ($ones > 0) {
                $words[] = $ones === 5 ? 'lăm' : $digits[$ones];
            }
        } elseif ($ones > 0) {
            if ($hundreds > 0 || (!$isHighestGroup && $number < 100)) {
                $words[] = 'lẻ';
            }
            $words[] = $ones === 5 && ($hundreds > 0 || $tens > 0) ? 'lăm' : $digits[$ones];
        }

        return trim(implode(' ', $words));
    }

    public function previewQuote(PdfTemplate $pdfTemplate, Quote $quote)
    {
        return redirect()->route('admin.pdf-templates.render-default.quote', ['quote' => $quote->id]);
    }
}
