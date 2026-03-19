<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductAddon;

class GenerateProductAddons extends Command
{
    protected $signature = 'addons:generate {--clear : Xóa addons cũ trước khi tạo mới}';
    protected $description = 'Tự động tạo addons từ cùng danh mục với giảm giá random 1-5% (tối đa 6 addon/sản phẩm)';

    public function handle()
    {
        $this->info("Bắt đầu tạo addons (tối đa 6 addon/sản phẩm, giảm 1-5%)...");
        
        // Xóa addons cũ nếu có option --clear
        if ($this->option('clear')) {
            ProductAddon::truncate();
            $this->info("Đã xóa tất cả addons cũ.");
        }
        
        $products = Product::with('category')->get();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();
        
        $totalCreated = 0;
        
        foreach ($products as $product) {
            // Lấy tối đa 6 sản phẩm khác cùng danh mục (random)
            $addons = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->take(6)
                ->get();
            
            foreach ($addons as $addon) {
                // Kiểm tra xem addon đã tồn tại chưa
                $exists = ProductAddon::where('product_id', $product->id)
                    ->where('addon_product_id', $addon->id)
                    ->exists();
                
                if (!$exists) {
                    ProductAddon::create([
                        'product_id' => $product->id,
                        'addon_product_id' => $addon->id,
                        'addon_price' => $addon->price, // Giữ giá gốc
                        'discount_percent' => rand(1, 5), // Random 1-5%
                    ]);
                    $totalCreated++;
                }
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Hoàn thành! Đã tạo {$totalCreated} addons.");
        
        return Command::SUCCESS;
    }
}
