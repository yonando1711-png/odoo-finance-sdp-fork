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
        Schema::table('print_logs', function (Blueprint $table) {
            $table->integer('kuitansi_print_count')->default(0);
            $table->text('kuitansi_pembayaran')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_logs', function (Blueprint $table) {
            $table->dropColumn(['kuitansi_print_count', 'kuitansi_pembayaran']);
        });
    }
};
