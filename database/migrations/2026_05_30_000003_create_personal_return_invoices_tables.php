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
        Schema::create('personal_return_invoices', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('invoice_no');
            $blueprint->string('customer_name');
            $blueprint->string('to_name');
            $blueprint->date('date_returned');
            $blueprint->text('description')->nullable();
            $blueprint->decimal('total_amount', 15, 2);
            $blueprint->decimal('paid_amount', 15, 2);
            $blueprint->decimal('due_amount', 15, 2);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('personal_return_invoice_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('personal_return_invoice_id')->constrained('personal_return_invoices')->onDelete('cascade');
            $blueprint->string('item_name');
            $blueprint->boolean('is_small_bales')->default(false);
            $blueprint->boolean('is_big_bales')->default(false);
            $blueprint->integer('no_of_bales');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_return_invoice_items');
        Schema::dropIfExists('personal_return_invoices');
    }
};
