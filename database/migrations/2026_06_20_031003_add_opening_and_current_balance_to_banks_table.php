<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 2)->default(0.00)->after('account_number');
            $table->decimal('current_balance', 15, 2)->default(0.00)->after('opening_balance');
            $table->string('status')->default('Active')->after('branch');
            $table->softDeletes()->after('updated_at');
        });

        // Copy existing balance to opening_balance and current_balance
        DB::table('banks')->update([
            'opening_balance' => DB::raw('balance'),
            'current_balance' => DB::raw('balance'),
        ]);

        // Drop the old balance column
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0.00)->after('account_number');
        });

        DB::table('banks')->update([
            'balance' => DB::raw('opening_balance'),
        ]);

        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['opening_balance', 'current_balance', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
