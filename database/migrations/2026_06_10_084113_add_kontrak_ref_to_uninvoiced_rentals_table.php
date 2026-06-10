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
        Schema::table('uninvoiced_rentals', function (Blueprint $table) {
            $table->string('kontrak_ref')->nullable()->after('nomor_kontrak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uninvoiced_rentals', function (Blueprint $table) {
            $table->dropColumn('kontrak_ref');
        });
    }
};
