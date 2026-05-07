<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained()->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->after('warehouse_id')->constrained()->onDelete('set null');
            $table->foreignId('purchase_order_item_id')->nullable()->after('batch_id')->constrained()->onDelete('set null');
            $table->decimal('unit_cost', 10, 2)->nullable()->after('balance_after');
            $table->string('reference_number')->nullable()->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn([
                'warehouse_id', 'batch_id', 'purchase_order_item_id', 'unit_cost', 'reference_number'
            ]);
        });
    }
};
