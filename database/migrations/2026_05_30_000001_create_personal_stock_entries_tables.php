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
        Schema::create('personal_stock_entries', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('supplier_name');
            $blueprint->string('container_no')->nullable();
            $blueprint->string('serial_no')->nullable();
            $blueprint->date('date_added');
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('personal_stock_entry_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('personal_stock_entry_id')->constrained('personal_stock_entries')->onDelete('cascade');
            $blueprint->enum('bale_type', ['small', 'big']);
            $blueprint->integer('no_of_bales');
            $blueprint->string('item_name');
            $blueprint->string('company');
            $blueprint->decimal('weight', 10, 2);
            $blueprint->decimal('rate', 15, 2);
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_stock_entry_items');
        Schema::dropIfExists('personal_stock_entries');
    }
};
