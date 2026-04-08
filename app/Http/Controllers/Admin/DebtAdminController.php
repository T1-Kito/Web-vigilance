<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use Illuminate\Http\Request;

class DebtAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Debt::query()->with(['salesOrder.quote'])->orderByDesc('created_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('debt_code', 'like', '%' . $q . '%')
                    ->orWhereHas('salesOrder', function ($so) use ($q) {
                        $so->where('sales_order_code', 'like', '%' . $q . '%')
                            ->orWhere('invoice_company_name', 'like', '%' . $q . '%')
                            ->orWhere('receiver_name', 'like', '%' . $q . '%')
                            ->orWhere('customer_tax_code', 'like', '%' . $q . '%');
                    });
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $debts = $query->paginate(20)->withQueryString();

        return view('admin.debts.index', compact('debts'));
    }

    public function show(Debt $debt)
    {
        $debt->load(['salesOrder.quote']);

        return view('admin.debts.show', compact('debt'));
    }

    public function update(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'collected_amount' => ['required', 'numeric', 'min:0'],
            'collected_at' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $currentPaid = (float) ($debt->paid_amount ?? 0);
        $collectNow = (float) ($validated['collected_amount'] ?? 0);
        $paid = min($currentPaid + $collectNow, (float) $debt->total_amount);
        $remaining = max(0, (float) $debt->total_amount - $paid);

        $effectiveDueDate = $validated['due_date'] ?? $debt->due_date;
        if ($remaining <= 0) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        } elseif (!empty($effectiveDueDate) && now()->toDateString() > (string) $effectiveDueDate) {
            $status = 'overdue';
        } else {
            $status = 'unpaid';
        }

        $debt->update([
            'paid_amount' => $paid,
            'remaining_amount' => $remaining,
            'status' => $status,
            'due_date' => $effectiveDueDate,
            'last_paid_at' => $collectNow > 0 ? ($validated['collected_at'] ?? now()) : $debt->last_paid_at,
            'note' => $validated['note'] ?? $debt->note,
        ]);

        if ($debt->salesOrder) {
            $debt->salesOrder->update([
                'paid_amount' => $debt->paid_amount,
                'payment_status' => $debt->status,
                'payment_due_date' => $debt->due_date,
                'paid_at' => $debt->last_paid_at,
                'payment_note' => $debt->note,
            ]);
        }

        return redirect()->route('admin.debts.show', $debt)->with('success', 'Đã cập nhật công nợ.');
    }
}
