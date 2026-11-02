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
        Schema::create('uninvoiced_short_terms', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cust')->nullable();
            $table->string('nomor_so')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('nomor_po')->nullable();
            $table->string('nomor_kontrak')->nullable();
            $table->string('kontrak_ref')->nullable();
            $table->string('nama_user')->nullable();
            $table->string('nopol')->nullable();
            $table->string('model')->nullable();
            $table->string('tahun_mobil')->nullable();
            $table->string('chassis')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('tanggal_periode_belum_cetak')->nullable();
            $table->string('start_rental_period')->nullable();
            $table->string('end_rental_period')->nullable();
            $table->decimal('price_di_so', 15, 2)->nullable();
            $table->decimal('duration_price', 15, 2)->nullable();
            $table->string('invoice_period')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('rental_method')->nullable();
            $table->string('first_invoice_date')->nullable();
            $table->string('area_pemakaian_unit')->nullable();
            $table->string('invoice_pic')->nullable();
            $table->string('recipient_bank')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('id_tku')->nullable();
            $table->string('kode_transaksi')->nullable();
            $table->text('address')->nullable();
            $table->text('tax_address')->nullable();
            $table->timestamps();
        });

        Schema::create('uninvoiced_sold_outs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cust')->nullable();
            $table->string('nomor_so')->nullable()->index();
            $table->string('status')->nullable();
            $table->string('nomor_po')->nullable();
            $table->string('nomor_kontrak')->nullable();
            $table->string('kontrak_ref')->nullable();
            $table->string('nama_user')->nullable();
            $table->string('nopol')->nullable();
            $table->string('model')->nullable();
            $table->string('tahun_mobil')->nullable();
            $table->string('chassis')->nullable();
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('tanggal_periode_belum_cetak')->nullable();
            $table->string('start_rental_period')->nullable();
            $table->string('end_rental_period')->nullable();
            $table->decimal('price_di_so', 15, 2)->nullable();
            $table->decimal('duration_price', 15, 2)->nullable();
            $table->string('invoice_period')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('rental_method')->nullable();
            $table->string('first_invoice_date')->nullable();
            $table->string('area_pemakaian_unit')->nullable();
            $table->string('invoice_pic')->nullable();
            $table->string('recipient_bank')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('id_tku')->nullable();
            $table->string('kode_transaksi')->nullable();
            $table->text('address')->nullable();
            $table->text('tax_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uninvoiced_short_terms');
        Schema::dropIfExists('uninvoiced_sold_outs');
    }
};
