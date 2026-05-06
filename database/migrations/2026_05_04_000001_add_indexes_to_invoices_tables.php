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
        $tables = ['invoice_drivers', 'invoice_rentals', 'invoice_others', 'invoice_vehicles'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->index('invoice_date');
                $table->index('partner_name');
                $table->index('ref');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['invoice_drivers', 'invoice_rentals', 'invoice_others', 'invoice_vehicles'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropIndex(['invoice_date']);
                $table->dropIndex(['partner_name']);
                $table->dropIndex(['ref']);
            });
        }
    }
};
