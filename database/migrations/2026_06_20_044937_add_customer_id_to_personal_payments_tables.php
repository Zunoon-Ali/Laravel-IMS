<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('personal_payments_received', 'customer_id')) {
            Schema::table('personal_payments_received', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('invoice_no')->constrained('personal_customers')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('personal_payments_sent', 'customer_id')) {
            Schema::table('personal_payments_sent', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('invoice_no')->constrained('personal_customers')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('personal_return_invoices', 'customer_id')) {
            Schema::table('personal_return_invoices', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('invoice_no')->constrained('personal_customers')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('personal_payments_received', 'customer_id')) {
            Schema::table('personal_payments_received', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }

        if (Schema::hasColumn('personal_payments_sent', 'customer_id')) {
            Schema::table('personal_payments_sent', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }

        if (Schema::hasColumn('personal_return_invoices', 'customer_id')) {
            Schema::table('personal_return_invoices', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }
    }
};
