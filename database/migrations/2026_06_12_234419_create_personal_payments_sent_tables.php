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
        Schema::create('personal_payments_sent', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('invoice_no')->unique();
            $blueprint->string('customer_name');
            $blueprint->string('to_name');
            $blueprint->date('date_sent');
            $blueprint->decimal('cash_amount', 15, 2)->default(0);
            $blueprint->decimal('total_amount', 15, 2);
            $blueprint->decimal('paid_amount', 15, 2);
            $blueprint->decimal('due_amount', 15, 2);
            $blueprint->text('description')->nullable();
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('personal_payment_sent_cheques', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('personal_payment_sent_id')->constrained('personal_payments_sent')->onDelete('cascade');
            $blueprint->string('bank_name');
            $blueprint->string('check_no');
            $blueprint->date('due_date');
            $blueprint->string('to_name');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->timestamps();
        });

        Schema::create('personal_payment_sent_onlines', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('personal_payment_sent_id')->constrained('personal_payments_sent')->onDelete('cascade');
            $blueprint->string('bank_name');
            $blueprint->string('name');
            $blueprint->date('payment_date');
            $blueprint->string('from_name');
            $blueprint->string('to_name');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_payment_sent_onlines');
        Schema::dropIfExists('personal_payment_sent_cheques');
        Schema::dropIfExists('personal_payments_sent');
    }
};
