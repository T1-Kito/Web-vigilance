<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orders = DB::table('orders')->orderBy('id')->get();

        foreach ($orders as $order) {
            $baseCode = trim((string) ($order->order_code ?? ''));
            if ($baseCode === '') {
                $baseCode = 'VK' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
            }

            $quoteCode = $baseCode;
            $suffix = 1;
            while (DB::table('quotes')->where('quote_code', $quoteCode)->exists()) {
                $suffix++;
                $quoteCode = $baseCode . '-' . $suffix;
            }

            $quoteId = DB::table('quotes')->insertGetId([
                'user_id' => $order->user_id,
                'source_order_id' => $order->id,
                'quote_code' => $quoteCode,
                'receiver_name' => $order->receiver_name,
                'receiver_phone' => $order->receiver_phone,
                'receiver_address' => $order->receiver_address,
                'invoice_company_name' => $order->invoice_company_name,
                'invoice_address' => $order->invoice_address,
                'customer_tax_code' => $order->customer_tax_code,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'customer_contact_person' => $order->customer_contact_person,
                'staff_code' => $order->staff_code,
                'sales_name' => $order->sales_name,
                'discount_percent' => (float) ($order->discount_percent ?? 0),
                'vat_percent' => (float) ($order->vat_percent ?? 8),
                'note' => $order->note,
                'status' => match ((string) ($order->status ?? 'pending')) {
                    'processing', 'completed' => 'won',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                },
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);

            $items = DB::table('order_items')->where('order_id', $order->id)->get();
            foreach ($items as $item) {
                DB::table('quote_items')->insert([
                    'quote_id' => $quoteId,
                    'product_id' => $item->product_id,
                    'quantity' => (int) ($item->quantity ?? 1),
                    'price' => (float) ($item->price ?? 0),
                    'unit' => $item->unit ?? null,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('quote_items')->truncate();
        DB::table('quotes')->truncate();
    }
};
