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
        $tables = [
            'invoice_subscriptions',
            'invoice_rentals',
            'invoice_drivers',
            'invoice_vehicles',
            'invoice_others'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'invoice_pic')) {
                    $table->string('invoice_pic')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'invoice_subscriptions',
            'invoice_rentals',
            'invoice_drivers',
            'invoice_vehicles',
            'invoice_others'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'invoice_pic')) {
                    $table->dropColumn('invoice_pic');
                }
            });
        }
    }
};
