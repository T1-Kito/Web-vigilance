<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', (string) $request->query('type', ''));
        $q = trim((string) $request->query('q', ''));

        $query = PurchaseOrder::query()->with('items')->orderByDesc('created_at');
        if (in_array($status, ['order', 'return'], true)) {
            $query->where('order_type', $status);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('code', 'like', '%' . $q . '%')
                    ->orWhere('supplier_name', 'like', '%' . $q . '%')
                    ->orWhere('supplier_code', 'like', '%' . $q . '%')
                    ->orWhere('supplier_tax_code', 'like', '%' . $q . '%');
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $allOrders = PurchaseOrder::query()->with('items')->get();
        $totalOrders = $allOrders->count();
        $totalAmount = $allOrders->sum(function ($order) {
            return $order->items->sum(function ($item) {
                $amount = (float) ($item->amount ?? 0);
                $tax = (float) ($item->tax_percent ?? 0);
                return $amount + ($amount * $tax / 100);
            });
        });

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartLabels[] = $day->format('d/m');
            $dayTotal = PurchaseOrder::query()
                ->whereDate('created_at', $day->toDateString())
                ->with('items')
                ->get()
                ->sum(function ($order) {
                    return $order->items->sum(function ($item) {
                        $amount = (float) ($item->amount ?? 0);
                        $tax = (float) ($item->tax_percent ?? 0);
                        return $amount + ($amount * $tax / 100);
                    });
                });
            $chartValues[] = (float) $dayTotal;
        }

        return view('admin.purchase_orders.index', compact(
            'orders',
            'status',
            'q',
            'totalOrders',
            'totalAmount',
            'chartLabels',
            'chartValues'
        ));
    }

    public function create()
    {
        return view('admin.purchase_orders.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $order = DB::transaction(function () use ($validated) {
            $todayCount = PurchaseOrder::query()->whereDate('created_at', now()->toDateString())->count() + 1;
            $code = 'PO-' . now()->format('ymd') . '-' . str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);

            $order = PurchaseOrder::create([
                'code' => $code,
                'order_type' => $validated['order_type'] ?? 'order',
                'supplier_code' => $validated['supplier_code'] ?? null,
                'supplier_name' => $validated['supplier_name'],
                'supplier_address' => $validated['supplier_address'] ?? null,
                'supplier_tax_code' => $validated['supplier_tax_code'] ?? null,
                'supplier_contact_name' => $validated['supplier_contact_name'] ?? null,
                'supplier_contact_phone' => $validated['supplier_contact_phone'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'delivery_location' => $validated['delivery_location'] ?? null,
                'buyer_name' => $validated['buyer_name'] ?? null,
                'buyer_position' => $validated['buyer_position'] ?? null,
                'credit_days' => (int) ($validated['credit_days'] ?? 0),
                'payment_currency' => $validated['payment_currency'] ?? 'VND',
                'order_type' => $validated['order_type'] ?? 'order',
                'po_number' => $validated['po_number'] ?? null,
                'debt_note' => $validated['debt_note'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $idx => $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['unit_price'] ?? 0);
                $amount = $qty * $price;

                $order->items()->create([
                    'line_no' => $idx + 1,
                    'item_name' => $item['item_name'],
                    'unit' => $item['unit'] ?? null,
                    'warranty_period' => $item['warranty_period'] ?? null,
                    'quantity' => $qty,
                    'serial_number' => $item['serial_number'] ?? null,
                    'unit_price' => $price,
                    'tax_percent' => (float) ($item['tax_percent'] ?? 0),
                    'amount' => round($amount, 2),
                    'note' => $item['note'] ?? null,
                ]);
            }

            return $order;
        });

        return redirect()->route('admin.purchase-orders.show', $order)->with('success', 'Đã tạo đơn mua hàng.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');
        return view('admin.purchase_orders.show', ['order' => $purchaseOrder]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');
        return view('admin.purchase_orders.edit', ['order' => $purchaseOrder]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $this->validatePayload($request);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $purchaseOrder->update([
                'supplier_code' => $validated['supplier_code'] ?? null,
                'supplier_name' => $validated['supplier_name'],
                'supplier_address' => $validated['supplier_address'] ?? null,
                'supplier_tax_code' => $validated['supplier_tax_code'] ?? null,
                'supplier_contact_name' => $validated['supplier_contact_name'] ?? null,
                'supplier_contact_phone' => $validated['supplier_contact_phone'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'delivery_location' => $validated['delivery_location'] ?? null,
                'buyer_name' => $validated['buyer_name'] ?? null,
                'buyer_position' => $validated['buyer_position'] ?? null,
                'credit_days' => (int) ($validated['credit_days'] ?? 0),
                'payment_currency' => $validated['payment_currency'] ?? 'VND',
                'order_type' => $validated['order_type'] ?? 'order',
                'po_number' => $validated['po_number'] ?? null,
                'debt_note' => $validated['debt_note'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $idx => $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $price = (float) ($item['unit_price'] ?? 0);
                $amount = $qty * $price;

                $purchaseOrder->items()->create([
                    'line_no' => $idx + 1,
                    'item_name' => $item['item_name'],
                    'unit' => $item['unit'] ?? null,
                    'warranty_period' => $item['warranty_period'] ?? null,
                    'quantity' => $qty,
                    'serial_number' => $item['serial_number'] ?? null,
                    'unit_price' => $price,
                    'tax_percent' => (float) ($item['tax_percent'] ?? 0),
                    'amount' => round($amount, 2),
                    'note' => $item['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('success', 'Đã cập nhật đơn mua hàng.');
    }

    public function exportPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items');

        $pdf = Pdf::loadView('admin.purchase_orders.pdf', ['order' => $purchaseOrder])
            ->setPaper('a4', 'portrait');

        $filename = 'phieu-mua-hang-' . ($purchaseOrder->po_number ?: $purchaseOrder->code) . '.pdf';
        return $pdf->download($filename);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('admin.purchase-orders.index')->with('success', 'Đã xóa đơn.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'supplier_code' => 'nullable|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'supplier_address' => 'nullable|string|max:1000',
            'supplier_tax_code' => 'nullable|string|max:100',
            'supplier_contact_name' => 'nullable|string|max:255',
            'supplier_contact_phone' => 'nullable|string|max:50',
            'delivery_date' => 'nullable|date',
            'delivery_location' => 'nullable|string|max:255',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_position' => 'nullable|string|max:255',
            'credit_days' => 'nullable|integer|min:0|max:3650',
            'payment_currency' => 'nullable|string|max:20',
            'order_type' => 'nullable|string|in:order,return',
            'po_number' => 'nullable|string|max:255',
            'debt_note' => 'nullable|string|max:1000',
            'note' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.unit' => 'nullable|string|max:100',
            'items.*.warranty_period' => 'nullable|string|max:100',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.serial_number' => 'nullable|string|max:255',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.note' => 'nullable|string|max:255',
        ]);
    }
}
