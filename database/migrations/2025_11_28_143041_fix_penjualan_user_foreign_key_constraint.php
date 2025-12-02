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
        Schema::table('penjualan', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['id_user']);
            
            // Recreate foreign key with RESTRICT instead of CASCADE
            // This prevents deletion of user if there are related sales
            $table->foreign('id_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan', function (Blueprint $table) {
            // Drop the restrict constraint
            $table->dropForeign(['id_user']);
            
            // Restore original cascade constraint
            $table->foreign('id_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
