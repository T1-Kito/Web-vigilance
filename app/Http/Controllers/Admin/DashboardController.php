<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\RepairForm;
use App\Models\Warranty;
use App\Models\WarrantyClaim;

class DashboardController extends Controller
{
    public function index()
    {
        $ordersTotal = Order::query()->count();
        $ordersPending = Order::query()->whereIn('status', ['pending', 'processing'])->count();
        $ordersCompleted = Order::query()->where('status', 'completed')->count();

        $customersCount = Customer::query()->count();
        $productsCount = Product::query()->count();

        $warrantiesTotal = Warranty::query()->count();
        $warrantiesActive = Warranty::query()->where('status', 'active')->count();

        $claimsPending = WarrantyClaim::query()->where('claim_status', 'pending')->count();
        $claimsInProgress = WarrantyClaim::query()->where('claim_status', 'in_progress')->count();
        $claimsOpen = WarrantyClaim::query()->whereIn('claim_status', ['pending', 'approved', 'in_progress'])->count();

        $repairFormsTotal = RepairForm::query()->count();
        $borrowActive = BorrowRequest::query()->whereIn('status', ['borrowing', 'processing', 'proposed', 'overdue'])->count();

        $recentOrders = Order::query()
            ->with(['items'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'ordersTotal',
            'ordersPending',
            'ordersCompleted',
            'customersCount',
            'productsCount',
            'warrantiesTotal',
            'warrantiesActive',
            'claimsPending',
            'claimsInProgress',
            'claimsOpen',
            'repairFormsTotal',
            'borrowActive',
            'recentOrders',
        ));
    }
}
