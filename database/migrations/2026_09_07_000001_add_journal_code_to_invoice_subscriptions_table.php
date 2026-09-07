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
        Schema::table('invoice_subscriptions', function (Blueprint $table) {
            $table->string('journal_code', 20)->nullable()->index()->after('invoice_name');
            $table->string('journal_name', 100)->nullable()->after('journal_code');
        });

        // Backfill existing rows as INVRS
        DB::table('invoice_subscriptions')
            ->whereNull('journal_code')
            ->update([
                'journal_code' => 'INVRS',
                'journal_name' => 'Invoice Sewa Subscription',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['journal_code']);
            $table->dropColumn(['journal_code', 'journal_name']);
        });
    }
};
