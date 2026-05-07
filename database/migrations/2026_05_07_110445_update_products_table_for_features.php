<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('sku');
            $table->string('qr_code')->nullable()->after('barcode');
            $table->text('description')->nullable()->after('name');
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->string('weight')->nullable()->after('unit');
            $table->string('dimensions')->nullable()->after('weight');
            $table->boolean('track_batches')->default(false)->after('dimensions');
            $table->boolean('track_expiry')->default(false)->after('track_batches');
            $table->string('abc_class')->default('C')->after('track_expiry');
            $table->integer('reorder_quantity')->default(0)->after('min_stock_level');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('reorder_quantity');
            $table->string('currency')->default('USD')->after('tax_rate');
            $table->boolean('is_active')->default(true)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'barcode', 'qr_code', 'description', 'cost_price', 'weight', 
                'dimensions', 'track_batches', 'track_expiry', 'abc_class', 
                'reorder_quantity', 'tax_rate', 'currency', 'is_active'
            ]);
        });
    }
};
