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
        Schema::create('personal_payment_received_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('personal_payment_received_id');
            $table->foreign('personal_payment_received_id', 'p_recv_items_fk')
                ->references('id')
                ->on('personal_payments_received')
                ->onDelete('cascade');
            $table->string('bale_type'); // 'small' or 'big'
            $table->string('item_name');
            $table->string('company')->nullable();
            $table->integer('no_of_bales')->default(0);
            $table->decimal('weight', 12, 2)->default(0);
            $table->decimal('rate', 12, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_payment_received_items');
    }
};
