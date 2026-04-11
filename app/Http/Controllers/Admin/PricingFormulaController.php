<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingFormulaSetting;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingFormulaController extends Controller
{
    public function edit()
    {
        $setting = PricingFormulaSetting::current();

        return view('admin.pricing-formula.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'list_multiplier' => 'required|numeric|min:0.01|max:100',
            'retail_discount_percent' => 'required|numeric|min:0|max:100',
            'agent_markup_1_5_percent' => 'required|numeric|min:0|max:1000',
            'agent_markup_6_10_percent' => 'required|numeric|min:0|max:1000',
            'agent_markup_over_10_percent' => 'required|numeric|min:0|max:1000',
        ]);

        $setting = PricingFormulaSetting::current();
        $setting->fill($data);
        $setting->updated_by = auth()->id();
        $setting->save();

        return back()->with('success', 'Đã cập nhật công thức giá thành công.');
    }
}
