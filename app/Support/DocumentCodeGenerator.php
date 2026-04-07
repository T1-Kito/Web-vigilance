<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class DocumentCodeGenerator
{
    /**
     * Generate monthly sequential code.
     * Example: BG202604-0001
     */
    public static function next(Builder $query, string $column, string $prefix): string
    {
        $ym = now()->format('Ym');
        $base = $prefix . $ym . '-';

        $latest = (clone $query)
            ->where($column, 'like', $base . '%')
            ->orderByDesc($column)
            ->value($column);

        $seq = 1;
        if (is_string($latest) && preg_match('/^(?:' . preg_quote($base, '/') . ')(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        do {
            $candidate = $base . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $exists = (clone $query)->where($column, $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $seq++;
        } while (true);
    }
}
