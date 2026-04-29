<?php

namespace App\Support;

class LineVatCalculator
{
    public static function totals(iterable $items, string $priceField, ?float $discountPercent = 0, ?float $fallbackVatPercent = 8): array
    {
        $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
        $fallbackVatPercent = max(0, (float) ($fallbackVatPercent ?? 0));

        $subTotal = 0.0;
        $vatAmount = 0.0;
        $lines = [];

        foreach ($items as $item) {
            $qty = (float) ($item->quantity ?? 0);
            $unitPrice = (float) ($item->{$priceField} ?? 0);
            $lineTotal = max(0, $qty * $unitPrice);
            $lineAfterDiscount = $lineTotal * (1 - ($discountPercent / 100));
            $vatPercent = max(0, (float) ($item->vat_percent ?? $fallbackVatPercent));
            $lineVatAmount = $lineAfterDiscount * ($vatPercent / 100);

            $subTotal += $lineTotal;
            $vatAmount += $lineVatAmount;
            $lines[] = [
                'item' => $item,
                'line_total' => $lineTotal,
                'line_after_discount' => $lineAfterDiscount,
                'vat_percent' => $vatPercent,
                'vat_amount' => $lineVatAmount,
                'line_total_after_vat' => $lineAfterDiscount + $lineVatAmount,
                'vat_label' => self::vatLabel($vatPercent),
            ];
        }

        $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));

        return [
            'sub_total' => $subTotal,
            'discount_percent' => $discountPercent,
            'after_discount' => $afterDiscount,
            'vat_amount' => $vatAmount,
            'total' => $afterDiscount + $vatAmount,
            'lines' => $lines,
        ];
    }

    public static function vatLabel(float $vatPercent): string
    {
        if (abs($vatPercent) < 0.00001) {
            return 'KCT/0%';
        }

        return rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.') . '%';
    }
}
