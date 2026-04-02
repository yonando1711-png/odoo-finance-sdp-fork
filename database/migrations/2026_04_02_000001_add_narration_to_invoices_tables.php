<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['invoice_rentals', 'invoice_drivers', 'invoice_others', 'invoice_vehicles'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->text('narration')->nullable()->after('ref');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['invoice_rentals', 'invoice_drivers', 'invoice_others', 'invoice_vehicles'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('narration');
                });
            }
        }
    }
};
