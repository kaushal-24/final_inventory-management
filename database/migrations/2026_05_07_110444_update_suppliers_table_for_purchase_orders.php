<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->string('contact_person')->nullable()->after('email');
            $table->string('tax_id')->nullable()->after('address');
            $table->string('payment_terms')->nullable()->after('tax_id');
            $table->decimal('credit_limit', 12, 2)->nullable()->after('payment_terms');
            $table->boolean('is_active')->default(true)->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'email', 'contact_person', 'tax_id', 'payment_terms', 'credit_limit', 'is_active'
            ]);
        });
    }
};
