<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'performance_rating' => 'nullable|integer|min:1|max:5',
            'durability_rating' => 'nullable|integer|min:1|max:5',
            'content' => 'nullable|string|max:2000',
        ]);

        $product = Product::findOrFail($productId);

        // Kiểm tra đã đánh giá chưa
        $existing = Review::where('product_id', $productId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            // Cập nhật đánh giá cũ
            $existing->update([
                'rating' => $request->rating,
                'performance_rating' => $request->performance_rating,
                'durability_rating' => $request->durability_rating,
                'content' => $request->content,
            ]);
            return back()->with('success', 'Đã cập nhật đánh giá của bạn!');
        }

        // Tạo đánh giá mới
        Review::create([
            'product_id' => $productId,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'performance_rating' => $request->performance_rating,
            'durability_rating' => $request->durability_rating,
            'content' => $request->content,
            'is_purchased' => false, // Có thể check từ orders sau
        ]);

        return back()->with('success', 'Cảm ơn bạn đã đánh giá!');
    }

    public function reply(Request $request, $reviewId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $parentReview = Review::findOrFail($reviewId);

        Review::create([
            'product_id' => $parentReview->product_id,
            'user_id' => Auth::id(),
            'parent_id' => $reviewId,
            'rating' => $parentReview->rating,
            'content' => $request->content,
            'is_approved' => true,
        ]);

        return back()->with('success', 'Đã gửi trả lời!');
    }
}
