<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\PdfTemplate;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    private function guestOrderCodes(): array
    {
        return array_values(array_unique(array_filter((array) session()->get('guest_order_codes', []))));
    }

    private function authorizeOrderView(Order $order): void
    {
        return;
    }

    private function authorizeOrderConfirm(Order $order): void
    {
        $user = Auth::user();

        if ($user) {
            if (($user->role ?? null) === 'admin') {
                return;
            }
            if ((int) $order->user_id !== (int) $user->id) {
                abort(403);
            }
            return;
        }

        if ($order->user_id) {
            abort(403);
        }

        $codes = $this->guestOrderCodes();
        if (!in_array($order->order_code, $codes, true)) {
            abort(403);
        }
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->orderByDesc('created_at')->get();
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.index', compact('orders', 'categories'));
    }

    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Kiểm tra xem đơn hàng có tồn tại không
        if (!$order) {
            abort(404, 'Đơn hàng không tồn tại');
        }
        
        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem đơn hàng');
        }
        
        // So sánh với kiểu dữ liệu chính xác và thêm debug
        $orderUserId = (int)$order->user_id;
        $currentUserId = (int)$user->id;
        
        if ($orderUserId !== $currentUserId) {
            // Log để debug
            \Log::info('Order access denied', [
                'order_id' => $order->id,
                'order_user_id' => $orderUserId,
                'current_user_id' => $currentUserId,
                'user_email' => $user->email
            ]);
            
            abort(403, 'Bạn không có quyền xem đơn hàng này. Order User ID: ' . $orderUserId . ', Current User ID: ' . $currentUserId);
        }
        
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.show', compact('order', 'categories'));
    }

    public function lookup(Request $request)
    {
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.lookup', [
            'categories' => $categories,
            'order' => null,
        ]);
    }

    public function lookupSearch(Request $request)
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
        ], [
            'order_code.required' => 'Vui lòng nhập mã đơn hàng',
        ]);

        $orderCode = trim($validated['order_code']);

        $order = Order::with(['items.product'])
            ->where('order_code', $orderCode)
            ->first();

        if (!$order) {
            return back()->withInput()->with('lookup_error', 'Không tìm thấy đơn hàng với mã đã nhập.');
        }

        if (!Auth::check()) {
            $codes = (array) session()->get('guest_order_codes', []);
            $codes[] = (string) ($order->order_code ?: '');
            $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));
            session()->put('guest_order_codes', $codes);
        }

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.lookup', [
            'categories' => $categories,
            'order' => $order,
        ]);
    }

    public function history(Request $request)
    {
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();

        $user = Auth::user();
        if ($user) {
            $orders = Order::with(['items.product'])
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get();

            return view('orders.history', [
                'categories' => $categories,
                'orders' => $orders,
                'isGuest' => false,
            ]);
        }

        $codes = (array) session()->get('guest_order_codes', []);
        $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));

        $orders = collect();
        if (!empty($codes)) {
            $orders = Order::with(['items.product'])
                ->whereIn('order_code', $codes)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('orders.history', [
            'categories' => $categories,
            'orders' => $orders,
            'isGuest' => true,
        ]);
    }

    public function quote(string $orderCode)
    {
        $quote = Quote::with(['items.product', 'user'])->where('quote_code', $orderCode)->first();
        if ($quote) {
            $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
            $activeTemplate = PdfTemplate::query()
                ->where('type', 'quote')
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->first();

            if ($activeTemplate) {
                $data = $this->buildPublicQuoteTemplateData($quote);
                $templateView = 'admin.pdf_templates.' . ($activeTemplate->view_name ?: 'preview');
                $html = view($templateView, $data)->render();
                $filename = 'bao-gia-' . ($quote->quote_code ?: ('quote-' . $quote->id)) . '.pdf';

                return Pdf::loadHTML($html)
                    ->setPaper('a4', 'portrait')
                    ->stream($filename);
            }

            return view('orders.quote', ['order' => $quote, 'categories' => $categories]);
        }

        $order = Order::with(['items.product', 'user'])->where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderView($order);

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();

        $activeTemplate = PdfTemplate::query()
            ->where('type', 'quote')
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->first();

        if ($activeTemplate) {
            $data = $this->buildPublicOrderTemplateData($order);
            $templateView = 'admin.pdf_templates.' . ($activeTemplate->view_name ?: 'preview');
            $html = view($templateView, $data)->render();
            $filename = 'bao-gia-' . ($order->order_code ?: ('order-' . $order->id)) . '.pdf';

            return Pdf::loadHTML($html)
                ->setPaper('a4', 'portrait')
                ->stream($filename);
        }

        return view('orders.quote', compact('order', 'categories'));
    }

    public function confirmFromQuote(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderConfirm($order);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.quote', ['orderCode' => $order->order_code])->with('error', 'Đơn hàng không ở trạng thái chờ xử lý.');
        }

        $validated = $request->validate([
            'payment_term' => ['required', 'in:full_advance,deposit,debt'],
            'payment_due_days' => ['nullable', 'integer', 'min:1', 'max:3650', 'required_if:payment_term,debt'],
            'deposit_percent' => ['nullable', 'numeric', 'min:0.01', 'max:100', 'required_if:payment_term,deposit'],
            'payment_note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,bank_transfer,mixed'],
        ], [
            'payment_term.required' => 'Vui lòng chọn điều khoản thanh toán.',
            'payment_due_days.required_if' => 'Vui lòng nhập hạn công nợ.',
            'deposit_percent.required_if' => 'Vui lòng nhập tỷ lệ đặt cọc.',
        ]);

        if (!empty($order->source_quote_id)) {
            $sourceQuote = Quote::query()->find($order->source_quote_id);
            if ($sourceQuote) {
                $sourceQuote->payment_term = (string) $validated['payment_term'];
                $sourceQuote->payment_due_days = $validated['payment_term'] === 'debt' ? (int) ($validated['payment_due_days'] ?? 0) : null;
                $sourceQuote->deposit_percent = $validated['payment_term'] === 'deposit' ? (float) ($validated['deposit_percent'] ?? 0) : null;
                $sourceQuote->payment_note = trim((string) ($validated['payment_note'] ?? '')) ?: null;
                $sourceQuote->save();
            }
        }

        if (!empty($validated['payment_method'])) {
            $order->payment_method = (string) $validated['payment_method'];
        }

        $order->status = 'processing';
        $order->save();

        return redirect()->route('orders.quote.success', ['orderCode' => $order->order_code]);
    }

    public function quoteSuccess(string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderView($order);

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.quote_success', compact('order', 'categories'));
    }

    private function buildPublicQuoteTemplateData(Quote $quote): array
    {
        $items = $quote->items ?? collect();
        $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
        $discountPercent = (float) ($quote->discount_percent ?? 0);
        $vatPercent = (float) ($quote->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $total = $afterDiscount + $vatAmount;

        $itemRows = '';
        foreach ($items as $idx => $item) {
            $line = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
            $img = (string) ($item->product->image ?? '');
            $imgPath = $img !== '' ? public_path('images/products/' . ltrim($img, '/')) : '';
            $itemRows .= '<tr>'
                . '<td class="t-center">' . ($idx + 1) . '</td>'
                . '<td><b>' . e((string) ($item->product->name ?? ('SP #' . $item->product_id))) . '</b></td>'
                . '<td class="t-center">' . e((string) ($item->quantity ?? 0)) . '</td>'
                . '<td class="t-center">' . ($imgPath !== '' && file_exists($imgPath) ? '<img src="' . e($imgPath) . '" alt="" style="max-width:60px; max-height:60px; object-fit:contain;">' : '') . '</td>'
                . '<td class="t-right">' . number_format((float) ($item->price ?? 0), 0, ',', '.') . '</td>'
                . '<td class="t-right">' . number_format($line, 0, ',', '.') . '</td>'
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
            'quote' => $quote,
            'customerName' => (string) ($quote->invoice_company_name ?: $quote->receiver_name),
            'taxCode' => (string) ($quote->customer_tax_code ?? ''),
            'address' => (string) (($quote->invoice_address ?: $quote->receiver_address) ?? ''),
            'phone' => (string) ($quote->customer_phone ?? $quote->receiver_phone ?? ''),
            'email' => (string) ($quote->customer_email ?? ''),
            'paymentTermLabel' => $paymentTermLabel,
            'paymentDueDays' => (string) ((int) ($quote->payment_due_days ?? 0)),
            'depositPercent' => rtrim(rtrim(number_format((float) ($quote->deposit_percent ?? 0), 2, '.', ''), '0'), '.'),
            'paymentNote' => $paymentNote,
            'subTotal' => number_format($subTotal, 0, ',', '.'),
            'vatAmount' => number_format($vatAmount, 0, ',', '.'),
            'total' => number_format($total, 0, ',', '.'),
            'totalInWords' => $this->numberToVietnameseWords((int) round($total)),
            'itemRows' => $itemRows,
        ];
    }

    private function buildPublicOrderTemplateData(Order $order): array
    {
        $items = $order->items ?? collect();
        $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
        $discountPercent = (float) ($order->discount_percent ?? 0);
        $vatPercent = (float) ($order->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $total = $afterDiscount + $vatAmount;

        $itemRows = '';
        foreach ($items as $idx => $item) {
            $line = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
            $img = (string) ($item->product->image ?? '');
            $imgPath = $img !== '' ? public_path('images/products/' . ltrim($img, '/')) : '';
            $itemRows .= '<tr>'
                . '<td class="t-center">' . ($idx + 1) . '</td>'
                . '<td><b>' . e((string) ($item->product->name ?? ('SP #' . $item->product_id))) . '</b></td>'
                . '<td class="t-center">' . e((string) ($item->quantity ?? 0)) . '</td>'
                . '<td class="t-center">' . ($imgPath !== '' && file_exists($imgPath) ? '<img src="' . e($imgPath) . '" alt="" style="max-width:60px; max-height:60px; object-fit:contain;">' : '') . '</td>'
                . '<td class="t-right">' . number_format((float) ($item->price ?? 0), 0, ',', '.') . '</td>'
                . '<td class="t-right">' . number_format($line, 0, ',', '.') . '</td>'
                . '</tr>';
        }

        $paymentTerm = (string) ($order->payment_term ?? 'full_advance');
        $paymentTermLabel = match ($paymentTerm) {
            'debt' => 'Công nợ theo hạn',
            'deposit' => 'Đặt cọc + phần còn lại',
            default => 'Thanh toán 100% trước giao hàng',
        };

        $paymentNote = (string) ($order->payment_note ?? '');
        if ($paymentTerm === 'deposit' && $paymentNote === '') {
            $depositPercent = rtrim(rtrim(number_format((float) ($order->deposit_percent ?? 0), 2, '.', ''), '0'), '.');
            $paymentNote = $depositPercent !== '' ? ($depositPercent . '% còn lại khi xong thanh toán') : 'Thanh toán phần còn lại khi xong thanh toán';
        }
        if ($paymentTerm === 'debt' && $paymentNote === '') {
            $days = (int) ($order->payment_due_days ?? 0);
            $paymentNote = $days > 0 ? ('Công nợ trong ' . $days . ' ngày') : 'Công nợ theo thỏa thuận';
        }

        return [
            'quote' => $order,
            'customerName' => (string) ($order->invoice_company_name ?: $order->receiver_name),
            'taxCode' => (string) ($order->customer_tax_code ?? ''),
            'address' => (string) (($order->invoice_address ?: $order->receiver_address) ?? ''),
            'phone' => (string) ($order->customer_phone ?? $order->receiver_phone ?? ''),
            'email' => (string) ($order->customer_email ?? ''),
            'paymentTermLabel' => $paymentTermLabel,
            'paymentDueDays' => (string) ((int) ($order->payment_due_days ?? 0)),
            'depositPercent' => rtrim(rtrim(number_format((float) ($order->deposit_percent ?? 0), 2, '.', ''), '0'), '.'),
            'paymentNote' => $paymentNote,
            'subTotal' => number_format($subTotal, 0, ',', '.'),
            'vatAmount' => number_format($vatAmount, 0, ',', '.'),
            'total' => number_format($total, 0, ',', '.'),
            'totalInWords' => $this->numberToVietnameseWords((int) round($total)),
            'itemRows' => $itemRows,
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

    public function quotePdf(string $orderCode)
    {
        $quote = Quote::with(['items.product', 'user'])->where('quote_code', $orderCode)->first();
        if ($quote) {
            $activeTemplate = PdfTemplate::query()
                ->where('type', 'quote')
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->first();

            if ($activeTemplate) {
                $data = $this->buildPublicQuoteTemplateData($quote);
                $templateView = 'admin.pdf_templates.' . ($activeTemplate->view_name ?: 'preview');
                $html = view($templateView, $data)->render();

                $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
            } else {
                $pdf = Pdf::loadView('orders.quote_pdf', [
                    'order' => $quote,
                ])->setPaper('a4', 'portrait');
            }

            $filename = 'bao-gia-' . ($quote->quote_code ?: ('quote-' . $quote->id)) . '-' . now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);
        }

        $order = Order::with(['items.product', 'user'])->where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }

        $this->authorizeOrderView($order);

        $pdf = Pdf::loadView('orders.quote_pdf', [
            'order' => $order,
        ])->setPaper('a4', 'portrait');

        $filename = 'bao-gia-' . ($order->order_code ?: ('order-' . $order->id)) . '-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}
