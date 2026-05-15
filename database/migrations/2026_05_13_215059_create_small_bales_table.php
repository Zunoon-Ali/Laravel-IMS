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
        Schema::create('small_bales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('stock')->default(0);
            $table->integer('production')->default(0);
            $table->integer('sale')->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('weight', 12, 2)->default(0);
            $table->decimal('rate', 12, 2)->default(0);
            $table->date('date');
            $table->string('supplier')->nullable();
            $table->string('category')->nullable();
            $table->string('warehouseLocation')->nullable();
            $table->string('sku')->nullable();
            $table->string('status')->nullable();
            $table->integer('quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('small_bales');
    }
};
