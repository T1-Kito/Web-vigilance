<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::query()->with(['items', 'debt'])->orderByDesc('created_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('sales_order_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_tax_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $q . '%');
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $paymentStatus = trim((string) $request->query('payment_status', ''));
        if ($paymentStatus !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        $salesOrders = $query->paginate(20)->withQueryString();

        return view('admin.sales_orders.index', [
            'salesOrders' => $salesOrders,
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product', 'quote', 'debt']);

        $deliveries = Delivery::query()
            ->where('sales_order_id', $salesOrder->id)
            ->orderByDesc('created_at')
            ->get();

        $invoices = Invoice::query()
            ->where('sales_order_id', $salesOrder->id)
            ->orderByDesc('created_at')
            ->get();

        $totalOrdered = (int) $salesOrder->items->sum('quantity');
        $totalDelivered = (int) DeliveryItem::query()
            ->whereIn('sales_order_item_id', $salesOrder->items->pluck('id')->all())
            ->sum('quantity');

        $subTotal = (float) $salesOrder->items->sum(function ($item) {
            return (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
        });
        $discountPercent = (float) ($salesOrder->discount_percent ?? 0);
        $vatPercent = (float) ($salesOrder->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $orderTotal = $afterDiscount + $vatAmount;
        $paidAmount = (float) ($salesOrder->debt->paid_amount ?? $salesOrder->paid_amount ?? 0);
        $remainingDebt = max(0, $orderTotal - $paidAmount);

        $salesOrderTemplates = \App\Models\DocumentTemplate::query()
            ->whereIn('type', ['sales_order', 'quote', 'shared'])
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.sales_orders.show', [
            'salesOrder' => $salesOrder,
            'deliveries' => $deliveries,
            'invoices' => $invoices,
            'totalOrdered' => $totalOrdered,
            'totalDelivered' => $totalDelivered,
            'orderTotal' => $orderTotal,
            'paidAmount' => $paidAmount,
            'remainingDebt' => $remainingDebt,
            'salesOrderTemplates' => $salesOrderTemplates,
        ]);
    }

    public function exportPdf(SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product', 'quote']);

        $pdf = Pdf::loadView('admin.sales_orders.pdf', [
            'salesOrder' => $salesOrder,
        ])->setPaper('a4', 'portrait');

        $filename = 'don-hang-' . ($salesOrder->sales_order_code ?: ('so-' . $salesOrder->id)) . '-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    public function createDelivery(SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product']);

        $deliveredMap = DeliveryItem::query()
            ->whereIn('sales_order_item_id', $salesOrder->items->pluck('id')->all())
            ->selectRaw('sales_order_item_id, SUM(quantity) as qty')
            ->groupBy('sales_order_item_id')
            ->pluck('qty', 'sales_order_item_id');

        return view('admin.sales_orders.create_delivery', [
            'salesOrder' => $salesOrder,
            'deliveredMap' => $deliveredMap,
        ]);
    }

    public function storeDelivery(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_address' => ['required', 'string', 'max:2000'],
            'delivery_reason' => ['required', 'string', 'max:2000'],
            'delivery_location' => ['required', 'string', 'max:2000'],
            'source_document_ref' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $salesOrder->load('items');
        $soItems = $salesOrder->items->keyBy('id');

        $deliveredMap = DeliveryItem::query()
            ->whereIn('sales_order_item_id', $salesOrder->items->pluck('id')->all())
            ->selectRaw('sales_order_item_id, SUM(quantity) as qty')
            ->groupBy('sales_order_item_id')
            ->pluck('qty', 'sales_order_item_id');

        $lines = [];
        foreach ($validated['items'] as $row) {
            $itemId = (int) $row['sales_order_item_id'];
            $qty = (int) $row['quantity'];
            if ($qty <= 0) {
                continue;
            }

            $soItem = $soItems->get($itemId);
            if (!$soItem) {
                continue;
            }

            $alreadyDelivered = (int) ($deliveredMap[$itemId] ?? 0);
            $remaining = max(0, (int) $soItem->quantity - $alreadyDelivered);
            if ($remaining <= 0) {
                continue;
            }

            if ($qty > $remaining) {
                return back()->withInput()->with('error', 'Số lượng xuất vượt quá còn lại của sản phẩm.');
            }

            $lines[] = [
                'sales_order_item_id' => $itemId,
                'product_id' => (int) $soItem->product_id,
                'quantity' => $qty,
            ];
        }

        if (count($lines) === 0) {
            return back()->withInput()->with('error', 'Vui lòng nhập số lượng xuất hợp lệ.');
        }

        $delivery = DB::transaction(function () use ($request, $salesOrder, $validated, $lines) {
            $delivery = Delivery::create([
                'sales_order_id' => $salesOrder->id,
                'delivery_code' => DocumentCodeGenerator::next(Delivery::query(), 'delivery_code', 'PX'),
                'status' => 'confirmed',
                'delivered_at' => now(),
                'receiver_name' => $validated['receiver_name'],
                'receiver_address' => $validated['receiver_address'],
                'delivery_reason' => $validated['delivery_reason'],
                'delivery_location' => $validated['delivery_location'],
                'source_document_ref' => $validated['source_document_ref'] ?? null,
            ]);

            foreach ($lines as $line) {
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'sales_order_item_id' => $line['sales_order_item_id'],
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            $totalOrdered = (int) $salesOrder->items()->sum('quantity');
            $totalDelivered = (int) DeliveryItem::query()
                ->whereIn('sales_order_item_id', $salesOrder->items()->pluck('id')->all())
                ->sum('quantity');

            if ($totalDelivered >= $totalOrdered && $totalOrdered > 0) {
                $salesOrder->status = 'completed';
            } elseif ($totalDelivered > 0) {
                $salesOrder->status = 'processing';
            }
            $salesOrder->save();

            ActivityLogger::log(
                'sales_order.delivery.create',
                $delivery,
                'Tạo phiếu xuất kho từ đơn bán ngoài',
                [
                    'delivery_code' => $delivery->delivery_code,
                    'sales_order_id' => $salesOrder->id,
                    'sales_order_code' => $salesOrder->sales_order_code,
                    'items' => $lines,
                ],
                $request
            );

            return $delivery;
        });

        return redirect()->route('admin.deliveries.show', $delivery)->with('success', 'Đã tạo phiếu xuất kho thành công.');
    }

    public function createInvoice(SalesOrder $salesOrder)
    {
        $salesOrder->load(['items.product']);

        return view('admin.sales_orders.create_invoice', [
            'salesOrder' => $salesOrder,
        ]);
    }

    public function updatePayment(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'payment_status' => ['required', Rule::in(['unpaid', 'partial', 'paid', 'overdue'])],
            'payment_due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'payment_note' => ['nullable', 'string', 'max:500'],
        ]);

        $salesOrder->load('items');
        $subTotal = (float) $salesOrder->items->sum(function ($item) {
            return (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
        });
        $discountPercent = (float) ($salesOrder->discount_percent ?? 0);
        $vatPercent = (float) ($salesOrder->vat_percent ?? 8);
        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
        $vatAmount = $afterDiscount * ($vatPercent / 100);
        $total = $afterDiscount + $vatAmount;

        $paid = min((float) $validated['paid_amount'], $total);
        $remaining = max(0, $total - $paid);
        $status = $validated['payment_status'];

        if ($paid <= 0 && $remaining > 0) {
            $status = 'unpaid';
        } elseif ($remaining <= 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }

        $salesOrder->update([
            'paid_amount' => $paid,
            'payment_status' => $status,
            'payment_due_date' => $validated['payment_due_date'] ?? $salesOrder->payment_due_date,
            'paid_at' => $validated['paid_at'] ?? $salesOrder->paid_at,
            'payment_note' => $validated['payment_note'] ?? $salesOrder->payment_note,
        ]);

        $salesOrder->debt()?->update([
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'status' => $status,
            'due_date' => $validated['payment_due_date'] ?? $salesOrder->payment_due_date,
            'last_paid_at' => $validated['paid_at'] ?? $salesOrder->paid_at,
            'note' => $validated['payment_note'] ?? $salesOrder->payment_note,
        ]);

        return back()->with('success', 'Đã cập nhật công nợ đơn hàng.');
    }

    public function storeInvoice(Request $request, SalesOrder $salesOrder)
    {
        $validated = $request->validate([
            'issued_at' => ['nullable', 'date'],
            'status' => ['required', 'in:issued,cancelled,draft'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $salesOrder->load(['items']);
        if ($salesOrder->items->count() === 0) {
            return back()->withInput()->with('error', 'Đơn bán không có sản phẩm để xuất hóa đơn.');
        }

        $invoice = DB::transaction(function () use ($request, $salesOrder, $validated) {
            $subTotal = (float) $salesOrder->items->sum(function ($item) {
                return (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
            });

            $discountPercent = (float) ($validated['discount_percent'] ?? 0);
            $vatPercent = (float) ($validated['vat_percent'] ?? 8);

            $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
            $vatAmount = $afterDiscount * ($vatPercent / 100);
            $total = $afterDiscount + $vatAmount;

            $invoice = Invoice::create([
                'sales_order_id' => $salesOrder->id,
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

            foreach ($salesOrder->items as $item) {
                $qty = (int) ($item->quantity ?? 0);
                $unitPrice = (float) ($item->unit_price ?? 0);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'sales_order_item_id' => $item->id,
                    'product_id' => (int) $item->product_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $qty * $unitPrice,
                    'unit' => $item->unit,
                ]);
            }

            ActivityLogger::log(
                'sales_order.invoice.create',
                $invoice,
                'Phát hành hóa đơn từ đơn hàng',
                [
                    'invoice_code' => $invoice->invoice_code,
                    'sales_order_id' => $salesOrder->id,
                    'sales_order_code' => $salesOrder->sales_order_code,
                    'total_amount' => $invoice->total_amount,
                ],
                $request
            );

            return $invoice;
        });

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Đã phát hành hóa đơn thành công.');
    }
}
