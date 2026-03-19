<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        $user = auth()->user();
        $productId = $request->input('product_id');
        $wishlist = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->first();
        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['status' => 'removed']);
        } else {
            Wishlist::create(['user_id' => $user->id, 'product_id' => $productId]);
            return response()->json(['status' => 'added']);
        }
    }

    public function index()
    {
        $user = auth()->user();
        $wishlists = \App\Models\Wishlist::with('product')->where('user_id', $user->id)->get();
        $categories = \App\Models\Category::with(['children' => function($q) {
            $q->with('children');
        }])->whereNull('parent_id')->ordered()->get();
        return view('wishlist.index', compact('wishlists', 'categories'));
    }
} 