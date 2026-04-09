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
        Schema::table('invoice_drivers', function (Blueprint $table) {
            $table->string('partner_npwp')->nullable();
            $table->string('contract_ref')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_drivers', function (Blueprint $table) {
            $table->dropColumn(['partner_npwp', 'contract_ref']);
        });
    }
};
