<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureAutoIncrementPrimaryId('migrations');
        $this->ensureAutoIncrementPrimaryId('product_price_tiers');
    }

    public function down(): void
    {
        // Intentionally left empty: this migration repairs broken local schema state.
    }

    private function ensureAutoIncrementPrimaryId(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $database = DB::getDatabaseName();

        $column = DB::selectOne(
            "SELECT COLUMN_TYPE, COLUMN_KEY, EXTRA
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'id'
             LIMIT 1",
            [$database, $table]
        );

        if (!$column) {
            return;
        }

        $columnType = (string) ($column->COLUMN_TYPE ?? 'bigint(20) unsigned');
        $isPrimary = strtoupper((string) ($column->COLUMN_KEY ?? '')) === 'PRI';
        $isAutoIncrement = str_contains(strtolower((string) ($column->EXTRA ?? '')), 'auto_increment');

        if (!$isPrimary) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
        }

        if (!$isAutoIncrement) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$columnType} NOT NULL AUTO_INCREMENT");
        }
    }
};
