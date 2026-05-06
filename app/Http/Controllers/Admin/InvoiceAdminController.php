<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Quote;
use App\Models\SalesOrder;
use App\Services\MisaMeInvoiceService;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use App\Support\LineVatCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with(['order', 'salesOrder', 'sourceInvoice'])->orderByDesc('created_at');

        $orderCode = trim((string) $request->query('order_code', ''));
        if ($orderCode !== '') {
            $query->where(function ($q) use ($orderCode) {
                $q->whereHas('order', function ($sub) use ($orderCode) {
                    $sub->where('order_code', 'like', '%' . $orderCode . '%');
                })->orWhereHas('salesOrder', function ($sub) use ($orderCode) {
                    $sub->where('sales_order_code', 'like', '%' . $orderCode . '%');
                });
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $quickTab = trim((string) $request->query('tab', 'all'));
        if ($quickTab === 'active') {
            $query->where('status', 'issued');
        } elseif ($quickTab === 'referenced') {
            $query->whereIn('status', ['adjusted', 'replaced']);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'filters' => [
                'order_code' => $orderCode,
                'status' => $status,
                'tab' => $quickTab,
            ],
        ]);
    }

    public function createFromOrder(Order $order)
    {
        $order->load(['items.product']);

        $nameWarnings = $this->buildNameMismatchWarningsForOrder($order);

        return view('admin.invoices.create', [
            'order' => $order,
            'nameWarnings' => $nameWarnings,
        ]);
    }

    public function createInbound(Request $request)
    {
        $query = \App\Models\PurchaseOrder::query()->with('items')->latest();
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', '%' . $q . '%')
                    ->orWhere('supplier_name', 'like', '%' . $q . '%')
                    ->orWhere('supplier_tax_code', 'like', '%' . $q . '%');
            });
        }

        $status = trim((string) $request->query('status', ''));
        $purchaseOrders = $query->paginate(20)->withQueryString();

        return view('admin.invoices.inbound-index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => compact('q', 'status'),
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'issued_at' => ['nullable', 'date'],
            'status' => ['required', 'in:issued,cancelled,draft'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->load(['items']);
        if ($order->items->count() === 0) {
            return back()->withInput()->with('error', 'Đơn hàng không có sản phẩm để xuất hóa đơn.');
        }

        $invoice = DB::transaction(function () use ($request, $order, $validated) {
            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $vatPercent = (float) ($validated['vat_percent'] ?? 8);
            $totals = LineVatCalculator::totals($order->items, 'price', $discountPercent, $vatPercent);
            $subTotal = (float) $totals['sub_total'];
            $vatAmount = (float) $totals['vat_amount'];
            $total = (float) $totals['total'];

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'invoice_code' => DocumentCodeGenerator::next(Invoice::query(), 'invoice_code', 'HD'),
                'status' => $validated['status'],
                'issued_at' => $validated['issued_at'] ?? now(),
                'discount_percent' => $discountPercent,
                'vat_percent' => $vatPercent,
                'sub_total' => $subTotal,
                'vat_amount' => $vatAmount,
                'total_amount' => $total,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($order->items as $item) {
                $qty = (int) ($item->quantity ?? 0);
                $unitPrice = (float) ($item->price ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'order_item_id' => $item->id,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $qty * $unitPrice,
                    'unit' => $item->unit,
                ]);
            }

            ActivityLogger::log(
                'invoice.create',
                $invoice,
                'Phát hành hóa đơn từ đơn hàng',
                [
                    'invoice_code' => $invoice->invoice_code,
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => $invoice->total_amount,
                ],
                $request
            );

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Đã phát hành hóa đơn thành công.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['order.items.product', 'salesOrder.items.product', 'items.product', 'items.orderItem']);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
            'misaPublishedUrl' => null,
        ]);
    }

    public function verifyMisa(Request $request, Invoice $invoice)
    {
        try {
            $service = app(MisaMeInvoiceService::class);
            $verify = $service->verifyIssuedInvoice($invoice);
            $previewUrl = (string) data_get($verify, 'best.json.data', '');

            ActivityLogger::log(
                'invoice.misa.verify',
                $invoice,
                'Đối soát trạng thái hóa đơn MISA',
                [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_code,
                    'misa_ref_id' => $invoice->misa_ref_id,
                    'misa_transaction_id' => $invoice->misa_transaction_id,
                    'verify_best_status' => data_get($verify, 'best.status'),
                    'verify_successful' => data_get($verify, 'best.successful'),
                ],
                $request
            );

            return back()->with([
                'success' => 'Đã đối soát trạng thái hóa đơn trên MISA.',
                'misa_verify_result' => $verify,
                'misa_preview_url' => $previewUrl,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Đối soát MISA thất bại: ' . $e->getMessage());
        }
    }

    public function openMisa(Request $request, Invoice $invoice)
    {
        try {
            $service = app(MisaMeInvoiceService::class);
            $isDraftInvoice = (string) ($invoice->status ?? '') === 'draft';
            $url = $isDraftInvoice
                ? $service->getDraftViewUrl($invoice)
                : $service->getPublishedViewUrl($invoice);

            ActivityLogger::log(
                'invoice.misa.open',
                $invoice,
                $isDraftInvoice ? 'Mở liên kết hóa đơn nháp trên MISA' : 'Mở liên kết hóa đơn trên MISA',
                [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_code,
                    'invoice_status' => $invoice->status,
                    'misa_ref_id' => $invoice->misa_ref_id,
                    'misa_transaction_id' => $invoice->misa_transaction_id,
                ],
                $request
            );

            return redirect()->away($url);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Không mở được hóa đơn điện tử trên MISA: ' . $e->getMessage());
        }
    }

    public function issueMisaForSalesOrder(Request $request, SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product', 'quote', 'deliveries.items.product']);

        if ($salesOrder->deliveries()->count() === 0) {
            return back()->with('error', 'Chưa có phiếu xuất kho nên chưa thể tạo hóa đơn nháp MISA.');
        }

        $validated = $request->validate([
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'receiver_email' => ['nullable', 'string', 'max:255'],
        ]);

        $overrides = [
            'receiver_name' => trim((string) ($validated['receiver_name'] ?? '')),
            'receiver_email' => trim((string) ($validated['receiver_email'] ?? '')),
        ];

        $idempotencyKey = 'misa:sales_order:' . $salesOrder->id . ':action:preview';
        if (!Cache::add($idempotencyKey, now()->timestamp, 20)) {
            return back()->with('error', 'Hệ thống đang xử lý yêu cầu nháp cho đơn này. Vui lòng chờ vài giây rồi thử lại.');
        }

        try {
            $service = app(MisaMeInvoiceService::class);
            $result = $service->previewFromSalesOrder($salesOrder, $salesOrder->quote, $overrides);
            $previewUrl = (string) (data_get($result, 'preview_url') ?: data_get($result, 'view_url') ?: '');

            return back()->with([
                'success' => 'Đã tạo bản xem trước hóa đơn nháp MISA.',
                'misa_preview_url' => $previewUrl,
                'open_misa_tab' => $previewUrl !== '' ? $previewUrl : null,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Tạo hóa đơn nháp MISA thất bại: ' . $e->getMessage());
        } finally {
            Cache::forget($idempotencyKey);
        }
    }

    public function publishMisaForSalesOrder(Request $request, SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product', 'quote', 'deliveries.items.product']);

        if ($salesOrder->deliveries()->count() === 0) {
            return back()->with('error', 'Chưa có phiếu xuất kho nên chưa thể phát hành hóa đơn MISA.');
        }

        $validated = $request->validate([
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'receiver_email' => ['nullable', 'string', 'max:255'],
        ]);

        $overrides = [
            'receiver_name' => trim((string) ($validated['receiver_name'] ?? '')),
            'receiver_email' => trim((string) ($validated['receiver_email'] ?? '')),
        ];

        $idempotencyKey = 'misa:sales_order:' . $salesOrder->id . ':action:publish';
        if (!Cache::add($idempotencyKey, now()->timestamp, 20)) {
            return back()->with('error', 'Hệ thống đang xử lý yêu cầu phát hành cho đơn này. Vui lòng chờ vài giây rồi thử lại.');
        }

        try {
            $service = app(MisaMeInvoiceService::class);
            $result = $service->issueFromSalesOrder($salesOrder, $salesOrder->quote, $overrides);
            $invoice = $service->persistIssuedInvoice($salesOrder, $result);

            ActivityLogger::log(
                'invoice.misa.publish',
                $invoice,
                'Phát hành hóa đơn MISA',
                [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_code,
                    'sales_order_id' => $salesOrder->id,
                    'sales_order_code' => $salesOrder->sales_order_code,
                    'misa_ref_id' => $invoice->misa_ref_id,
                    'misa_transaction_id' => $invoice->misa_transaction_id,
                ],
                $request
            );

            return redirect()->route('admin.invoices.index', [
                'notice_success' => $this->buildMisaFlashMessage($invoice),
                'open_misa_tab' => $invoice->misa_publish_view_url ?: route('admin.invoices.open-misa', $invoice),
                'issued_invoice_id' => $invoice->id,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Phát hành hóa đơn MISA thất bại: ' . $e->getMessage());
        } finally {
            Cache::forget($idempotencyKey);
        }
    }

    private function buildMisaFlashMessage(Invoice $invoice): string
    {
        $prefix = 'Đã phát hành hóa đơn MISA thành công.';

        $invoiceNo = trim((string) ($invoice->misa_invoice_code ?: $invoice->invoice_code));
        $series = trim((string) ($invoice->misa_inv_series ?? ''));
        $refId = trim((string) ($invoice->misa_ref_id ?? ''));
        $transactionId = trim((string) ($invoice->misa_transaction_id ?? ''));

        $parts = [$prefix];
        if ($invoiceNo !== '') {
            $parts[] = 'Số hóa đơn: ' . $invoiceNo . '.';
        }
        if ($series !== '') {
            $parts[] = 'Ký hiệu: ' . $series . '.';
        }
        if ($refId !== '') {
            $parts[] = 'RefID: ' . $refId . '.';
        }
        if ($transactionId !== '') {
            $parts[] = 'Mã tra cứu: ' . $transactionId . '.';
        }

        return implode(' ', $parts);
    }

    public function createReference(Invoice $invoice, string $type)
    {
        if (!in_array($type, ['replacement', 'adjustment'], true)) {
            abort(404);
        }
        if ($invoice->status !== 'issued') {
            return back()->with('error', 'Chỉ hóa đơn đã phát hành mới được thay thế/điều chỉnh.');
        }

        $hasReplacementChild = $invoice->replacementChildren()
            ->where('reference_action', 'replacement')
            ->exists();
        if ($type === 'adjustment' && $hasReplacementChild) {
            return back()->with('error', 'Hóa đơn này đã có nghiệp vụ thay thế nên không thể điều chỉnh tiếp. Vui lòng chọn tờ đang hiệu lực mới nhất.');
        }

        $invoice->load(['salesOrder.items.product', 'salesOrder.quote', 'items.product', 'order', 'replacementChildren']);

        return view('admin.invoices.reference-create', [
            'invoice' => $invoice,
            'type' => $type,
        ]);
    }

    public function storeReference(Request $request, Invoice $invoice, string $type)
    {
        if (!in_array($type, ['replacement', 'adjustment'], true)) {
            abort(404);
        }
        if ($invoice->status !== 'issued') {
            return back()->with('error', 'Chỉ hóa đơn đã phát hành mới được thay thế/điều chỉnh.');
        }

        $hasReplacementChild = $invoice->replacementChildren()
            ->where('reference_action', 'replacement')
            ->exists();
        if ($type === 'adjustment' && $hasReplacementChild) {
            return back()->with('error', 'Hóa đơn này đã có nghiệp vụ thay thế nên không thể điều chỉnh tiếp. Vui lòng chọn tờ đang hiệu lực mới nhất.');
        }

        $validated = $request->validate([
            'preview_mode' => ['nullable', 'in:view,save'],
            'reason' => [$type === 'replacement' ? 'nullable' : 'required', 'string', 'max:1000'],
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'receiver_email' => ['nullable', 'string', 'max:255'],
            'buyer_legal_name' => ['nullable', 'string', 'max:255'],
            'buyer_tax_code' => ['nullable', 'string', 'max:100'],
            'buyer_address' => ['nullable', 'string', 'max:500'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.item_name' => ['required', 'string', 'max:255'],
            'line_items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'line_items.*.unit_price' => ['required', 'numeric'],
            'line_items.*.unit' => ['nullable', 'string', 'max:50'],
            'line_items.*.vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.description' => ['nullable', 'string', 'max:5000'],
            'line_items.*.item_code' => ['nullable', 'string', 'max:100'],
            'line_items.*.product_id' => ['nullable'],
        ]);

        $idempotencyKey = 'misa:invoice:' . $invoice->id . ':action:' . $type;
        if (!Cache::add($idempotencyKey, now()->timestamp, 20)) {
            return back()->with('error', 'Hệ thống đang xử lý yêu cầu. Vui lòng chờ vài giây rồi thử lại.');
        }

        try {
            $service = app(MisaMeInvoiceService::class);

            $series = trim((string) ($invoice->misa_inv_series ?? ''));
            $invNo = trim((string) ($invoice->misa_invoice_code ?? ''));
            $invDate = optional($invoice->misa_issued_at ?: $invoice->issued_at)->timezone(config('app.timezone'))->format('Y-m-d');

            if ($series === '' || $invNo === '' || !$invDate) {
                if (trim((string) ($invoice->misa_transaction_id ?? '')) === '') {
                    throw new \RuntimeException('Thiếu dữ liệu hóa đơn gốc và không có TransactionID để truy vấn MISA.');
                }
                $verify = $service->verifyIssuedInvoice($invoice);
                $json = (array) data_get($verify, 'best.json', []);
                $rawData = Arr::get($json, 'data', Arr::get($json, 'Data', []));
                if (is_array($rawData) && array_is_list($rawData)) {
                    $data = Arr::first($rawData) ?: [];
                } else {
                    $data = is_array($rawData) ? $rawData : [];
                }

                $series = $series !== '' ? $series : (string) (Arr::get($data, 'InvSeries', Arr::get($data, 'invSeries', Arr::get($data, 'OrgInvSeries', Arr::get($data, 'orgInvSeries', '')))));
                $invNo = $invNo !== '' ? $invNo : (string) (Arr::get($data, 'InvNo', Arr::get($data, 'invNo', Arr::get($data, 'InvoiceNo', Arr::get($data, 'invoiceNo', '')))));
                $invDateRaw = $invDate ?: (string) (Arr::get($data, 'InvDate', Arr::get($data, 'invDate', Arr::get($data, 'InvoiceDate', Arr::get($data, 'invoiceDate', '')))));
                $invDate = $invDateRaw ? date('Y-m-d', strtotime($invDateRaw)) : '';
            }

            if ($series === '' || $invNo === '' || !$invDate) {
                throw new \RuntimeException('Không lấy được đủ thông tin hóa đơn gốc (ký hiệu/số/ngày) để phát hành điều chỉnh theo đúng nghiệp vụ.');
            }

            $salesOrder = $invoice->salesOrder;
            if (!$salesOrder) {
                throw new \RuntimeException('Hóa đơn gốc chưa liên kết sales order để tạo hóa đơn thay thế/điều chỉnh.');
            }
            $salesOrder->loadMissing(['items.product', 'quote']);

            $orgDateDisplay = $invDate ? date('d/m/Y', strtotime($invDate)) : '';
            $notePrefix = $type === 'adjustment' ? 'Điều chỉnh' : 'Thay thế';
            $autoReferenceNote = trim($notePrefix . ' cho hóa đơn ký hiệu ' . $series . ', số ' . $invNo . ($orgDateDisplay !== '' ? (' ngày ' . $orgDateDisplay) : ''));
            $manualReason = trim((string) ($validated['reason'] ?? ''));
            $fullReferenceNote = trim($autoReferenceNote . ($manualReason !== '' ? ('. Lý do: ' . $manualReason) : ''));

            $reference = [
                'type' => $type,
                'reference_mode' => $type,
                'org_inv_series' => $series,
                'org_inv_no' => $invNo,
                'org_inv_date' => $invDate,
                'invoice_note' => $fullReferenceNote,
            ];

            $draftOnly = ($validated['preview_mode'] ?? 'view') === 'save';

            $result = $service->issueFromSalesOrder(
                $salesOrder,
                $salesOrder->quote,
                [
                    'receiver_name' => trim((string) ($validated['receiver_name'] ?? '')),
                    'receiver_email' => trim((string) ($validated['receiver_email'] ?? '')),
                    'buyer_legal_name' => trim((string) ($validated['buyer_legal_name'] ?? '')),
                    'buyer_tax_code' => trim((string) ($validated['buyer_tax_code'] ?? '')),
                    'buyer_address' => trim((string) ($validated['buyer_address'] ?? '')),
                    'line_items' => collect($validated['line_items'] ?? [])->map(function (array $row) {
                        return [
                            'product_id' => $row['product_id'] ?? null,
                            'item_code' => trim((string) ($row['item_code'] ?? '')),
                            'item_name' => trim((string) ($row['item_name'] ?? '')),
                            'description' => trim((string) ($row['description'] ?? '')),
                            'unit' => trim((string) ($row['unit'] ?? 'Cái')),
                            'quantity' => (float) ($row['quantity'] ?? 0),
                            'unit_price' => (float) ($row['unit_price'] ?? 0),
                            'vat_percent' => (float) ($row['vat_percent'] ?? 8),
                        ];
                    })->values()->all(),
                ],
                $draftOnly,
                $reference
            );

            $newInvoice = DB::transaction(function () use ($service, $salesOrder, $result, $invoice, $type, $validated, $draftOnly) {
                $newInvoice = $service->persistIssuedInvoice($salesOrder, $result);
                $newInvoice->source_invoice_id = $invoice->id;
                $newInvoice->reference_action = $type;
                $newInvoice->reference_reason = (string) $validated['reason'];
                $newInvoice->note = trim(($newInvoice->note ? ($newInvoice->note . ' | ') : '') . 'Nghiệp vụ: ' . ($type === 'replacement' ? 'Thay thế hóa đơn' : 'Điều chỉnh hóa đơn'));

                if (!$draftOnly) {
                    try {
                        $newInvoice->misa_publish_view_url = $service->getPublishedViewUrl($newInvoice);
                    } catch (\Throwable $e) {
                    }

                    $invoice->status = $type === 'replacement' ? 'replaced' : 'adjusted';
                    $invoice->save();
                }

                $newInvoice->save();

                return $newInvoice;
            });

            if ($draftOnly) {
                $draftUrl = (string) data_get($result, 'preview_url', data_get($result, 'view_url', ''));
                return back()->with([
                    'success' => 'Đã tạo hóa đơn nháp ' . ($type === 'replacement' ? 'thay thế' : 'điều chỉnh') . ' trên MISA (chưa phát hành).',
                    'misa_preview_url' => $draftUrl,
                    'open_misa_tab' => $draftUrl !== '' ? $draftUrl : route('admin.invoices.open-misa', $newInvoice),
                ])->withInput();
            }

            return redirect()->route('admin.invoices.index', [
                'notice_success' => 'Đã phát hành hóa đơn ' . ($type === 'replacement' ? 'thay thế' : 'điều chỉnh') . ' thành công.',
                'open_misa_tab' => $newInvoice->misa_publish_view_url ?: route('admin.invoices.open-misa', $newInvoice),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Phát hành hóa đơn thất bại: ' . $e->getMessage());
        } finally {
            Cache::forget($idempotencyKey);
        }
    }

    public function previewReference(Request $request, Invoice $invoice, string $type)
    {
        if (!in_array($type, ['replacement', 'adjustment'], true)) {
            abort(404);
        }
        if ($invoice->status !== 'issued') {
            return back()->with('error', 'Chỉ hóa đơn đã phát hành mới được thay thế/điều chỉnh.');
        }

        $validated = $request->validate([
            'preview_mode' => ['nullable', 'in:view,save'],
            'reason' => ['required', 'string', 'max:1000'],
            'receiver_name' => ['nullable', 'string', 'max:255'],
            'receiver_email' => ['nullable', 'string', 'max:255'],
            'buyer_legal_name' => ['nullable', 'string', 'max:255'],
            'buyer_tax_code' => ['nullable', 'string', 'max:100'],
            'buyer_address' => ['nullable', 'string', 'max:500'],
            'buyer_phone' => ['nullable', 'string', 'max:50'],
            'payment_method_name' => ['nullable', 'string', 'max:100'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.item_name' => ['required', 'string', 'max:255'],
            'line_items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'line_items.*.unit_price' => ['required', 'numeric', 'not_in:0'],
            'line_items.*.unit' => ['nullable', 'string', 'max:50'],
            'line_items.*.vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.description' => ['nullable', 'string', 'max:500'],
            'line_items.*.item_code' => ['nullable', 'string', 'max:100'],
            'line_items.*.product_id' => ['nullable'],
        ]);

        try {
            $service = app(MisaMeInvoiceService::class);

            $series = trim((string) ($invoice->misa_inv_series ?? ''));
            $invNo = trim((string) ($invoice->misa_invoice_code ?? $invoice->invoice_code ?? ''));
            $invDate = optional($invoice->issued_at)->timezone(config('app.timezone'))->format('Y-m-d');

            $salesOrder = $invoice->salesOrder;
            if (!$salesOrder) {
                throw new \RuntimeException('Hóa đơn gốc chưa liên kết sales order để tạo hóa đơn thay thế/điều chỉnh.');
            }

            $reference = [
                'type' => $type,
                'org_inv_series' => $series,
                'org_inv_no' => $invNo,
                'org_inv_date' => $invDate,
                'invoice_note' => (string) $validated['reason'],
            ];

            $draftOnly = true;

            $result = $service->issueFromSalesOrder(
                $salesOrder,
                $salesOrder->quote,
                [
                    'receiver_name' => trim((string) ($validated['receiver_name'] ?? '')),
                    'receiver_email' => trim((string) ($validated['receiver_email'] ?? '')),
                    'buyer_legal_name' => trim((string) ($validated['buyer_legal_name'] ?? '')),
                    'buyer_tax_code' => trim((string) ($validated['buyer_tax_code'] ?? '')),
                    'buyer_address' => trim((string) ($validated['buyer_address'] ?? '')),
                    'buyer_phone' => trim((string) ($validated['buyer_phone'] ?? '')),
                    'payment_method_name' => trim((string) ($validated['payment_method_name'] ?? 'TM/CK')),
                    'line_items' => collect($validated['line_items'] ?? [])->map(function (array $row) {
                        return [
                            'product_id' => $row['product_id'] ?? null,
                            'item_code' => trim((string) ($row['item_code'] ?? '')),
                            'item_name' => trim((string) ($row['item_name'] ?? '')),
                            'description' => trim((string) ($row['description'] ?? '')),
                            'unit' => trim((string) ($row['unit'] ?? 'Cái')),
                            'quantity' => (float) ($row['quantity'] ?? 0),
                            'unit_price' => (float) ($row['unit_price'] ?? 0),
                            'vat_percent' => (float) ($row['vat_percent'] ?? 8),
                        ];
                    })->values()->all(),
                ],
                $draftOnly,
                $reference
            );

            $url = (string) data_get($result, 'preview_url', data_get($result, 'published_url', ''));
            if ($url === '') {
                return back()->with('error', 'Không lấy được link xem trước từ MISA.')->withInput();
            }

            return back()->with('misa_preview_url', $url)->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Xem trước nháp thất bại: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        ActivityLogger::log(
            'invoice.delete.blocked',
            $invoice,
            'Từ chối xóa hóa đơn theo quy trình',
            [
                'invoice_id' => $invoice->id,
                'invoice_code' => $invoice->invoice_code,
            ],
            $request
        );

        return back()->with('error', 'Không được xóa hóa đơn theo quy trình. Nếu cần điều chỉnh, vui lòng thực hiện nghiệp vụ điều chỉnh/thay thế trên hệ thống hóa đơn điện tử.');
    }

    public function storeInbound(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'supplier_tax_code' => ['nullable', 'string', 'max:100'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $purchaseOrder = \App\Models\PurchaseOrder::query()->with('items')->findOrFail($validated['purchase_order_id']);
        $warnings = $this->buildInboundMismatchWarnings($purchaseOrder, $validated);
        $status = empty($warnings) ? 'matched' : 'received';

        return redirect()->route('admin.invoices.inbound.index')->with([
            'success' => 'Đã ghi nhận hóa đơn đầu vào (demo).',
            'received_invoice' => [
                ...$validated,
                'status' => $status,
                'purchase_order_code' => $purchaseOrder->code,
            ],
            'inbound_warnings' => $warnings,
        ]);
    }

    private function buildNameMismatchWarningsForOrder(Order $order): array
    {
        $warnings = [];
        $quote = $order->sourceQuote()->with(['items.product', 'convertedSalesOrder.items.product'])->first();

        if ($quote && $quote->items->isNotEmpty()) {
            $warnings = array_merge($warnings, $this->compareDocumentItemsByName(
                $quote->items,
                $order->items,
                'báo giá',
                'đơn bán',
                'quote_to_sales_order'
            ));
        }

        return $warnings;
    }

    private function compareDocumentItemsByName($leftItems, $rightItems, string $leftLabel, string $rightLabel, string $scope): array
    {
        $warnings = [];
        $leftItems = collect($leftItems)->values();
        $rightItems = collect($rightItems)->values();
        $maxCount = max($leftItems->count(), $rightItems->count());

        for ($i = 0; $i < $maxCount; $i++) {
            $leftItem = $leftItems->get($i);
            $rightItem = $rightItems->get($i);

            if (!$leftItem || !$rightItem) {
                continue;
            }

            $leftName = $this->normalizeCompareText((string) ($leftItem->product->name ?? $leftItem->item_name ?? ''));
            $rightName = $this->normalizeCompareText((string) ($rightItem->product->name ?? $rightItem->item_name ?? ''));

            if ($leftName === '' || $rightName === '') {
                continue;
            }

            if ($leftName !== $rightName) {
                $warnings[] = [
                    'scope' => $scope,
                    'type' => 'name_mismatch',
                    'severity' => $this->isLikelySimilarName($leftName, $rightName) ? 'warning' : 'danger',
                    'left_label' => $leftLabel,
                    'right_label' => $rightLabel,
                    'left_name' => (string) ($leftItem->product->name ?? $leftItem->item_name ?? ''),
                    'right_name' => (string) ($rightItem->product->name ?? $rightItem->item_name ?? ''),
                    'message' => 'Tên hàng không khớp giữa ' . $leftLabel . ' và ' . $rightLabel . ' ở dòng #' . ($i + 1) . '.',
                ];
            }
        }

        return $warnings;
    }

    private function buildInboundMismatchWarnings(\App\Models\PurchaseOrder $purchaseOrder, array $invoiceData): array
    {
        $warnings = [];
        $invoiceItems = collect($invoiceData['items'] ?? []);

        foreach ($purchaseOrder->items->values() as $index => $poItem) {
            $invoiceItem = $invoiceItems->get($index);
            if (!$invoiceItem) {
                continue;
            }

            $poName = $this->normalizeCompareText((string) ($poItem->item_name ?? ''));
            $invoiceName = $this->normalizeCompareText((string) ($invoiceItem['item_name'] ?? ''));

            if ($poName === '' || $invoiceName === '' || $poName === $invoiceName) {
                continue;
            }

            similar_text($poName, $invoiceName, $percent);
            $warnings[] = [
                'severity' => $percent >= 70 ? 'warning' : 'danger',
                'message' => 'Tên hàng đầu vào không khớp đơn mua hàng ở dòng #' . ($index + 1) . '.',
                'left_label' => 'Đơn mua hàng',
                'right_label' => 'Hóa đơn đầu vào',
                'left_name' => $poItem->item_name,
                'right_name' => (string) ($invoiceItem['item_name'] ?? ''),
            ];
        }

        return $warnings;
    }

    public function assignInboundToPurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'invoice_number' => ['required', 'string', 'max:255'],
        ]);

        $purchaseOrder = \App\Models\PurchaseOrder::query()->findOrFail($validated['purchase_order_id']);
        $warnings = session('inbound_warnings', []);
        $status = empty($warnings) ? 'assigned' : 'matched';

        return redirect()->route('admin.invoices.inbound.index')->with([
            'success' => 'Đã gán hóa đơn vào đơn mua hàng (demo).',
            'received_invoice' => [
                'invoice_number' => $validated['invoice_number'],
                'purchase_order_code' => $purchaseOrder->code,
                'status' => $status,
            ],
            'inbound_warnings' => $warnings,
        ]);
    }

    private function normalizeCompareText(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $value) ?? $value;

        return trim($value);
    }

    private function isLikelySimilarName(string $left, string $right): bool
    {
        similar_text($left, $right, $percent);

        return $percent >= 70;
    }
}
