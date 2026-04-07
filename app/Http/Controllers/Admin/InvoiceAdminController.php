<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Illuminate\Http\Request;
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

        return view('admin.invoices.create', [
            'order' => $order,
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
        $invoice->load(['order.items.product', 'items.product', 'items.orderItem']);

        return view('admin.invoices.show', compact('invoice'));
    }
}
