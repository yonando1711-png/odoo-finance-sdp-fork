<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_dp_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_dp_id')->constrained('invoice_dps')->onDelete('cascade');
            $table->integer('odoo_line_id')->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('price_unit', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('uom')->nullable();
            $table->string('product_name')->nullable();
            $table->string('serial_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_dp_lines');
    }
};
