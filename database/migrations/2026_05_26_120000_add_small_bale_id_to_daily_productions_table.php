<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->foreignId('small_bale_id')->nullable()->after('id')->constrained('small_bales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('daily_productions', function (Blueprint $table) {
            $table->dropForeign(['small_bale_id']);
            $table->dropColumn('small_bale_id');
        });
    }
};
