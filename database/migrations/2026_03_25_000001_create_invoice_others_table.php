<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_others', function (Blueprint $table) {
            $table->id();
            $table->string('odoo_id')->nullable()->index();
            $table->string('name')->unique();           // INVOT/2025/00239 or INVOW/2025/00640
            $table->string('partner_name');              // Customer name
            $table->date('invoice_date');
            $table->string('payment_term')->nullable();  // e.g. "45 Days"
            $table->string('ref')->nullable();           // Customer Reference
            $table->string('journal_name')->default('Invoice Other');
            $table->decimal('amount_untaxed', 15, 2)->default(0);
            $table->decimal('amount_tax', 15, 2)->default(0);
            $table->decimal('amount_total', 15, 2)->default(0);
            $table->string('partner_bank')->nullable();
            $table->string('manager_name')->nullable();  // BC Manager
            $table->string('spv_name')->nullable();      // BC SPV
            $table->text('partner_address')->nullable();
            $table->text('partner_address_complete')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_other_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_other_id')->constrained()->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('price_unit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_other_lines');
        Schema::dropIfExists('invoice_others');
    }
};
