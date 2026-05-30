<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_stock_entries', function (Blueprint $table) {
            $table->index('supplier_name');
        });

        Schema::table('personal_payments_received', function (Blueprint $table) {
            $table->index('customer_name');
            $table->index('invoice_no');
        });

        Schema::table('personal_return_invoices', function (Blueprint $table) {
            $table->index('customer_name');
            $table->index('invoice_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_stock_entries', function (Blueprint $table) {
            $table->dropIndex(['supplier_name']);
        });

        Schema::table('personal_payments_received', function (Blueprint $table) {
            $table->dropIndex(['customer_name']);
            $table->dropIndex(['invoice_no']);
        });

        Schema::table('personal_return_invoices', function (Blueprint $table) {
            $table->dropIndex(['customer_name']);
            $table->dropIndex(['invoice_no']);
        });
    }
};
