<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderInfoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // Tránh ONLY_FULL_GROUP_BY: tách thành 2 truy vấn (MST ưu tiên, sau đó fallback SĐT).
        $digits = preg_replace('/\D+/', '', $q);

        // Case 1: Có MST (customer_tax_code không rỗng)
        $taxSub = DB::table('orders')
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereNotNull('orders.customer_tax_code')
            ->whereRaw("TRIM(orders.customer_tax_code) <> ''")
            ->selectRaw("orders.customer_tax_code as customer_key")
            ->selectRaw("MAX(orders.receiver_name) as receiver_name")
            ->selectRaw("MAX(orders.customer_tax_code) as customer_tax_code")
            ->selectRaw("MAX(orders.receiver_phone) as receiver_phone")
            ->selectRaw("MAX(orders.customer_email) as customer_email")
            ->selectRaw("MAX(orders.invoice_company_name) as invoice_company_name")
            ->selectRaw("MAX(orders.invoice_address) as invoice_address")
            ->selectRaw("MAX(orders.receiver_address) as receiver_address")
            ->selectRaw("COUNT(DISTINCT orders.id) as orders_count")
            ->selectRaw("SUM(COALESCE(order_items.price,0) * COALESCE(order_items.quantity,0)) as total_amount")
            ->selectRaw("MAX(orders.created_at) as last_order_at")
            ->groupBy('orders.customer_tax_code');

        // Case 2: Không có MST => gom theo SĐT (receiver_phone)
        $phoneSub = DB::table('orders')
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where(function ($w) {
                $w->whereNull('orders.customer_tax_code')
                    ->orWhereRaw("TRIM(orders.customer_tax_code) = ''");
            })
            ->whereNotNull('orders.receiver_phone')
            ->whereRaw("TRIM(orders.receiver_phone) <> ''")
            ->selectRaw("orders.receiver_phone as customer_key")
            ->selectRaw("MAX(orders.receiver_name) as receiver_name")
            ->selectRaw("NULL as customer_tax_code")
            ->selectRaw("MAX(orders.receiver_phone) as receiver_phone")
            ->selectRaw("MAX(orders.customer_email) as customer_email")
            ->selectRaw("MAX(orders.invoice_company_name) as invoice_company_name")
            ->selectRaw("MAX(orders.invoice_address) as invoice_address")
            ->selectRaw("MAX(orders.receiver_address) as receiver_address")
            ->selectRaw("COUNT(DISTINCT orders.id) as orders_count")
            ->selectRaw("SUM(COALESCE(order_items.price,0) * COALESCE(order_items.quantity,0)) as total_amount")
            ->selectRaw("MAX(orders.created_at) as last_order_at")
            ->groupBy('orders.receiver_phone');

        // Apply search filter to both sub queries
        if ($q !== '') {
            $taxSub->where(function ($sub) use ($q, $digits) {
                $sub->where('orders.customer_tax_code', 'like', '%' . $q . '%');
                if ($digits !== '') {
                    $sub->orWhere('orders.customer_tax_code', 'like', '%' . $digits . '%');
                }
                $sub->orWhere('orders.receiver_name', 'like', '%' . $q . '%');
                $sub->orWhere('orders.receiver_phone', 'like', '%' . $q . '%');
            });

            $phoneSub->where(function ($sub) use ($q, $digits) {
                $sub->where('orders.receiver_phone', 'like', '%' . $q . '%');
                if ($digits !== '') {
                    $sub->orWhere('orders.receiver_phone', 'like', '%' . $digits . '%');
                }
                $sub->orWhere('orders.receiver_name', 'like', '%' . $q . '%');
            });
        }

        $union = $taxSub->unionAll($phoneSub);

        // Lấy top 20 (để tránh pagination count query với GROUP BY trong strict mode)
        $customers = DB::query()
            ->fromSub($union, 'u')
            ->select('*')
            ->orderByDesc('last_order_at')
            ->limit(20)
            ->get();

        return view('admin.customer_order_info.index', ['customers' => $customers, 'q' => $q]);
    }

    public function show(string $customerKey)
    {
        $customerKey = trim((string) $customerKey);
        if ($customerKey === '') {
            return redirect()->route('admin.customer-order-info.index')->with('error', 'Khóa khách hàng không hợp lệ.');
        }

        $customerKeyExpr = "COALESCE(NULLIF(customer_tax_code,''), NULLIF(receiver_phone,''))";

        $firstOrder = Order::query()
            ->whereRaw($customerKeyExpr . " = ?", [$customerKey])
            ->orderByDesc('created_at')
            ->first();

        if (!$firstOrder) {
            return redirect()->route('admin.customer-order-info.index')->with('error', 'Không tìm thấy lịch sử mua hàng.');
        }

        $orders = Order::with(['items'])
            ->whereRaw($customerKeyExpr . " = ?", [$customerKey])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.customer_order_info.show', [
            'customerKey' => $customerKey,
            'customer' => $firstOrder,
            'orders' => $orders,
        ]);
    }

    /**
     * Xóa tất cả đơn hàng theo khóa khách (MST ưu tiên, hoặc fallback theo SĐT).
     * Lưu ý: hành động này sẽ xóa dữ liệu đơn hàng trong DB (cascade order_items).
     */
    public function destroy(string $customerKey)
    {
        $customerKey = trim((string) $customerKey);
        if ($customerKey === '') {
            return redirect()->route('admin.customer-order-info.index')->with('error', 'Khóa khách hàng không hợp lệ.');
        }

        $deleted = Order::query()
            ->where('customer_tax_code', $customerKey)
            ->orWhere('receiver_phone', $customerKey)
            ->delete();

        return redirect()->route('admin.customer-order-info.index')
            ->with('success', 'Đã xóa thành công (đã xóa ' . (int) $deleted . ' đơn hàng).');
    }
}

