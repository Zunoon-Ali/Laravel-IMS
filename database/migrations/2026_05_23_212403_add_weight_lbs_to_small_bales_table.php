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
        Schema::table('small_bales', function (Blueprint $table) {
            $table->decimal('weight_lbs', 12, 2)->default(0.00)->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('small_bales', function (Blueprint $table) {
            $table->dropColumn('weight_lbs');
        });
    }
};
