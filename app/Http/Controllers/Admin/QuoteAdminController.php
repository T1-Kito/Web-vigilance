<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuoteAdminController extends Controller
{
    public function index(Request $request)
    {
        $defaultStatus = '';

        $statusOptions = [
            '' => 'Tất cả trạng thái',
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'won' => 'Chốt thành công',
            'lost' => 'Không chốt',
            'cancelled' => 'Đã hủy',
        ];

        $quotesQuery = Quote::query()
            ->with(['items', 'convertedSalesOrder'])
            ->orderByDesc('created_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $quotesQuery->where(function ($sub) use ($q) {
                $sub->where('quote_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_name', 'like', '%' . $q . '%')
                    ->orWhere('invoice_company_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_tax_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $q . '%')
                    ->orWhere('customer_phone', 'like', '%' . $q . '%');
            });
        }

        $customerName = trim((string) $request->query('customer_name', ''));
        if ($customerName !== '') {
            $quotesQuery->where(function ($sub) use ($customerName) {
                $sub->where('invoice_company_name', 'like', '%' . $customerName . '%')
                    ->orWhere('receiver_name', 'like', '%' . $customerName . '%');
            });
        }

        $taxCode = trim((string) $request->query('tax_code', ''));
        if ($taxCode !== '') {
            $quotesQuery->where('customer_tax_code', 'like', '%' . $taxCode . '%');
        }

        $status = trim((string) $request->query('status', $defaultStatus));
        if ($status !== '') {
            $quotesQuery->where('status', $status);
        }

        $orders = $quotesQuery->paginate(20)->withQueryString();

        return view('admin.quotes.index', [
            'orders' => $orders,
            'statusOptions' => $statusOptions,
            'defaultStatus' => $defaultStatus,
        ]);
    }

    public function create()
    {
        $statusOptions = [
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'won' => 'Chốt thành công',
            'lost' => 'Không chốt',
            'cancelled' => 'Đã hủy',
        ];

        $quote = new Quote([
            'status' => 'pending',
            'discount_percent' => 0,
            'vat_percent' => 8,
        ]);
        $quote->setRelation('items', collect());

        return view('admin.quotes.edit', [
            'order' => $quote,
            'statusOptions' => $statusOptions,
            'isCreate' => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:50'],
            'receiver_address' => ['required', 'string', 'max:2000'],

            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_address' => ['nullable', 'string', 'max:2000'],
            'customer_tax_code' => ['nullable', 'string', 'max:50'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_contact_person' => ['nullable', 'string', 'max:100'],

            'staff_code' => ['nullable', 'string', 'max:100'],
            'sales_name' => ['nullable', 'string', 'max:150'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'approved', 'won', 'lost', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $quote = DB::transaction(function () use ($validated) {
            $quote = Quote::create([
                'user_id' => auth()->id(),
                'quote_code' => $this->nextQuoteCode(),
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'receiver_address' => $validated['receiver_address'],
                'invoice_company_name' => $validated['invoice_company_name'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'customer_tax_code' => $validated['customer_tax_code'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_contact_person' => $validated['customer_contact_person'] ?? null,
                'staff_code' => $validated['staff_code'] ?? null,
                'sales_name' => $validated['sales_name'] ?? null,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'vat_percent' => $validated['vat_percent'] ?? 8,
                'valid_until' => $validated['valid_until'] ?? now()->addDays(15)->toDateString(),
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($validated['items'] as $row) {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => (int) $row['product_id'],
                    'quantity' => (int) $row['quantity'],
                    'price' => (float) $row['unit_price'],
                    'unit' => $row['unit'] ?? null,
                ]);
            }

            return $quote;
        });

        return redirect()->route('admin.quotes.edit', $quote)->with('success', 'Đã tạo báo giá thành công.');
    }

    public function show(Quote $quote)
    {
        return view('admin.quotes.show', [
            'quote' => $quote->load(['items.product', 'user', 'convertedSalesOrder']),
        ]);
    }

    public function edit(Quote $quote)
    {
        $statusOptions = [
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'won' => 'Chốt thành công',
            'lost' => 'Không chốt',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.quotes.edit', [
            'order' => $quote->load(['items.product']),
            'statusOptions' => $statusOptions,
        ]);
    }

    public function update(Request $request, Quote $quote)
    {
        if ((string) $quote->status === 'won' && Order::query()->where('source_quote_id', $quote->id)->exists()) {
            return back()->with('error', 'Báo giá đã chốt thành đơn hàng, không được phép sửa để đảm bảo tính pháp lý.');
        }

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:50'],
            'receiver_address' => ['required', 'string', 'max:2000'],

            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_address' => ['nullable', 'string', 'max:2000'],
            'customer_tax_code' => ['nullable', 'string', 'max:50'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_contact_person' => ['nullable', 'string', 'max:100'],

            'staff_code' => ['nullable', 'string', 'max:100'],
            'sales_name' => ['nullable', 'string', 'max:150'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['pending', 'approved', 'won', 'lost', 'cancelled'])],
            'note' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $before = [
            'status' => $quote->status,
            'discount_percent' => $quote->discount_percent,
            'vat_percent' => $quote->vat_percent,
        ];

        $oldStatus = (string) ($quote->status ?? 'pending');
        $newStatus = (string) ($validated['status'] ?? $oldStatus);
        $allowedTransitions = [
            'pending' => ['pending', 'approved', 'lost', 'cancelled'],
            'approved' => ['approved', 'won', 'lost', 'cancelled'],
            'lost' => ['lost'],
            'cancelled' => ['cancelled'],
            'won' => ['won'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [$oldStatus], true)) {
            return back()
                ->withInput()
                ->with('error', "Không thể chuyển trạng thái từ '{$oldStatus}' sang '{$newStatus}'.");
        }

        DB::transaction(function () use ($quote, $validated) {
            $quote->update([
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'receiver_address' => $validated['receiver_address'],
                'invoice_company_name' => $validated['invoice_company_name'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'customer_tax_code' => $validated['customer_tax_code'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_contact_person' => $validated['customer_contact_person'] ?? null,
                'staff_code' => $validated['staff_code'] ?? null,
                'sales_name' => $validated['sales_name'] ?? null,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'vat_percent' => $validated['vat_percent'] ?? 8,
                'valid_until' => $validated['valid_until'] ?? now()->addDays(15)->toDateString(),
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            $existingItems = $quote->items()->get()->keyBy('id');
            $keptItemIds = [];

            foreach ($validated['items'] as $row) {
                $itemId = isset($row['id']) ? (int) $row['id'] : 0;

                if ($itemId > 0) {
                    $item = $existingItems->get($itemId);
                    if ($item) {
                        $item->update([
                            'product_id' => (int) $row['product_id'],
                            'quantity' => (int) $row['quantity'],
                            'price' => (float) $row['unit_price'],
                            'unit' => $row['unit'] ?? null,
                        ]);
                        $keptItemIds[] = $item->id;
                    }
                    continue;
                }

                $created = QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => (int) $row['product_id'],
                    'quantity' => (int) $row['quantity'],
                    'price' => (float) $row['unit_price'],
                    'unit' => $row['unit'] ?? null,
                ]);
                $keptItemIds[] = $created->id;
            }

            if (!empty($keptItemIds)) {
                $quote->items()->whereNotIn('id', $keptItemIds)->delete();
            }
        });

        ActivityLogger::log(
            'quote.update',
            $quote,
            'Cập nhật báo giá: ' . ($quote->quote_code ?? ''),
            [
                'quote_code' => $quote->quote_code,
                'before' => $before,
                'after' => [
                    'status' => $quote->status,
                    'discount_percent' => $quote->discount_percent,
                    'vat_percent' => $quote->vat_percent,
                ],
            ],
            $request
        );

        return redirect()->route('admin.quotes.edit', $quote)->with('success', 'Đã cập nhật báo giá thành công.');
    }

    public function convertToOrder(Request $request, Quote $quote)
    {
        $existing = SalesOrder::query()->where('source_quote_id', $quote->id)->first();
        if ($existing) {
            return redirect()->route('admin.sales-orders.show', $existing)->with('success', 'Báo giá đã được chốt trước đó.');
        }

        if ((string) $quote->status !== 'approved') {
            return back()->with('error', 'Chỉ được chốt báo giá ở trạng thái Đã duyệt để đảm bảo quy trình kiểm soát nội bộ.');
        }

        $salesOrder = DB::transaction(function () use ($quote) {
            $salesOrderCode = DocumentCodeGenerator::next(SalesOrder::query(), 'sales_order_code', 'SO');

            $salesOrder = SalesOrder::create([
                'user_id' => $quote->user_id,
                'source_quote_id' => $quote->id,
                'sales_order_code' => $salesOrderCode,
                'receiver_name' => $quote->receiver_name,
                'receiver_phone' => $quote->receiver_phone,
                'receiver_address' => $quote->receiver_address,
                'invoice_company_name' => $quote->invoice_company_name,
                'invoice_address' => $quote->invoice_address,
                'customer_tax_code' => $quote->customer_tax_code,
                'customer_phone' => $quote->customer_phone,
                'customer_email' => $quote->customer_email,
                'customer_contact_person' => $quote->customer_contact_person,
                'staff_code' => $quote->staff_code,
                'sales_name' => $quote->sales_name,
                'discount_percent' => $quote->discount_percent,
                'vat_percent' => $quote->vat_percent,
                'note' => $quote->note,
                'status' => 'pending',
            ]);

            foreach ($quote->items as $qi) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $qi->product_id,
                    'quantity' => $qi->quantity,
                    'unit_price' => $qi->price,
                    'unit' => $qi->unit,
                ]);
            }

            $quote->update(['status' => 'won']);

            return $salesOrder;
        });

        ActivityLogger::log(
            'quote.convert_to_sales_order',
            $quote,
            'Chốt báo giá thành đơn bán hàng',
            [
                'quote_id' => $quote->id,
                'quote_code' => $quote->quote_code,
                'sales_order_id' => $salesOrder->id,
                'sales_order_code' => $salesOrder->sales_order_code,
            ],
            $request
        );

        return redirect()->route('admin.sales-orders.show', $salesOrder)->with('success', 'Đã chốt báo giá thành đơn bán hàng.');
    }

    private function nextQuoteCode(): string
    {
        return DocumentCodeGenerator::next(Quote::query(), 'quote_code', 'BG');
    }
}
