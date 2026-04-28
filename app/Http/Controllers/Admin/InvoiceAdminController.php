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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query()->with(['order', 'salesOrder'])->orderByDesc('created_at');

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

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'filters' => [
                'order_code' => $orderCode,
                'status' => $status,
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
            $subTotal = (float) $order->items->sum(function ($item) {
                return (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
            });

            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $vatPercent = (float) ($validated['vat_percent'] ?? 8);

            $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
            $vatAmount = $afterDiscount * ($vatPercent / 100);
            $total = $afterDiscount + $vatAmount;

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
            $url = $service->getPublishedViewUrl($invoice);

            ActivityLogger::log(
                'invoice.misa.open',
                $invoice,
                'Mở liên kết hóa đơn trên MISA',
                [
                    'invoice_id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_code,
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
        return $this->publishMisaForSalesOrder($request, $salesOrder);
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

            return redirect()->route('admin.invoices.index')->with([
                'success' => $this->buildMisaFlashMessage($invoice),
                'misa_invoice_result' => $result,
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
