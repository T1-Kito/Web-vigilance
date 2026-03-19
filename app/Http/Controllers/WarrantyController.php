<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warranty;
use App\Models\Category;
use App\Models\WarrantyClaim;
use App\Models\WarrantyStatus;

class WarrantyController extends Controller
{
    public function check()
    {
        $categories = Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();
        
        return view('warranty.check', compact('categories'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'serial_number' => 'required|string|max:255'
        ]);

        $serialNumber = trim($request->serial_number);
        $categories = Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();

        // Tìm bảo hành theo số seri
        $warranty = Warranty::with(['product', 'claims' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])
        ->where('serial_number', $serialNumber)
        ->first();

        if (!$warranty) {
            $errorMessage = 'Số seri này không tồn tại hoặc chưa có thông tin bảo hành: ' . $serialNumber;
            session()->flash('warranty_error', $errorMessage);

            return view('warranty.result', compact('categories', 'serialNumber', 'errorMessage'));
        }

        // Cập nhật trạng thái bảo hành
        $warranty->updateStatus();

        return view('warranty.result', compact('warranty', 'categories', 'serialNumber'));
    }

    public function claim(Request $request)
    {
        $request->validate([
            'warranty_id' => 'required|exists:warranties,id',
            'issue_description' => 'required|string|max:1000',
            'customer_complaint' => 'required|string|max:1000',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255'
        ]);

        $warranty = Warranty::findOrFail($request->warranty_id);

        // Kiểm tra xem có thể yêu cầu bảo hành không
        if (!$warranty->canClaimWarranty()) {
            return back()->with('error', 'Bảo hành không còn hiệu lực hoặc đã hết hạn!');
        }

        // Tạo số yêu cầu bảo hành
        $claimNumber = 'WC' . date('Ymd') . str_pad(WarrantyClaim::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        // Tạo yêu cầu bảo hành
        $claim = $warranty->claims()->create([
            'claim_number' => $claimNumber,
            'claim_date' => now()->toDateString(),
            'issue_description' => $request->issue_description,
            'customer_complaint' => $request->customer_complaint,
            'claim_status' => 'pending'
        ]);

        // Log status change
        $warranty->statuses()->create([
            'warranty_claim_id' => $claim->id,
            'status' => 'claim_created',
            'notes' => 'Khách hàng tạo yêu cầu bảo hành: ' . $claimNumber,
            'changed_by' => 'customer'
        ]);

        return redirect()->route('warranty.check')
            ->with('success', 'Yêu cầu bảo hành đã được gửi thành công! Số yêu cầu: ' . $claimNumber . '. Vui lòng kiểm tra lại bằng số seri: ' . $warranty->serial_number);
    }
}
