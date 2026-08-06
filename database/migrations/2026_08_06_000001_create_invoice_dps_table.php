<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_dps', function (Blueprint $table) {
            $table->id();
            $table->integer('odoo_id')->unique();
            $table->string('name')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('invoice_date_due')->nullable();
            $table->string('payment_term')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('partner_npwp')->nullable();
            $table->text('partner_address')->nullable();
            $table->string('invoice_pic')->nullable();
            $table->string('reserved_lot')->nullable();
            $table->string('ref')->nullable();
            $table->string('journal_name')->nullable();
            $table->string('payment_state')->default('not_paid');
            $table->string('state')->default('posted');
            $table->decimal('amount_untaxed', 15, 2)->default(0);
            $table->decimal('amount_tax', 15, 2)->default(0);
            $table->decimal('amount_total', 15, 2)->default(0);
            $table->string('partner_bank')->nullable();
            $table->string('bc_manager')->nullable();
            $table->string('bc_spv')->nullable();
            $table->text('narration')->nullable();
            $table->string('contract_ref')->nullable();
            $table->integer('print_count')->default(0);
            $table->timestamp('last_printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_dps');
    }
};
