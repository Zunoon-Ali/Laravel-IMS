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
        Schema::table('opened_bales', function (Blueprint $table) {
            // Drop old foreign key constraint
            $table->dropForeign(['container_id']);
            
            // Add new foreign key constraint with onDelete('set null')
            $table->foreign('container_id')
                ->references('id')
                ->on('containers')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opened_bales', function (Blueprint $table) {
            // Drop new foreign key constraint
            $table->dropForeign(['container_id']);
            
            // Re-add old constraint with onDelete('cascade')
            $table->foreign('container_id')
                ->references('id')
                ->on('containers')
                ->onDelete('cascade');
        });
    }
};
