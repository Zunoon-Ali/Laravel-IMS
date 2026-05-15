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
        Schema::create('opened_bales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->nullable()->constrained('containers')->onDelete('cascade');
            $table->string('containerNo');
            $table->date('date');
            $table->integer('opened');
            $table->integer('remaining');
            $table->decimal('stockLbs', 12, 2);
            $table->decimal('remainingLbs', 12, 2);
            $table->decimal('openValue', 15, 2);
            $table->decimal('remainingValue', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opened_bales');
    }
};
