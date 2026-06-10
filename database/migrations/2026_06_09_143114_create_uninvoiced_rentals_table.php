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
        Schema::create('uninvoiced_rentals', function (Blueprint $table) {
            $table->id();
            
            // Core references
            $table->string('kode_cust')->nullable();
            $table->string('nomor_so')->nullable();
            $table->string('nomor_po')->nullable();
            $table->string('nomor_kontrak')->nullable();
            $table->string('nama_user')->nullable();
            
            // Vehicle info
            $table->string('nopol')->nullable();
            $table->string('model')->nullable();
            $table->string('tahun_mobil')->nullable();
            $table->string('chassis')->nullable();
            
            // Rental details
            $table->string('start')->nullable(); // actual_start_rental
            $table->string('end')->nullable();   // actual_end_rental
            $table->string('tanggal_periode_belum_cetak')->nullable(); // earliest empty invoice_date
            $table->decimal('price_di_so', 15, 2)->nullable();
            $table->string('invoice_period')->nullable(); // rental_uom
            $table->string('payment_terms')->nullable();
            $table->string('rental_method')->nullable();
            $table->string('first_invoice_date')->nullable();
            
            // Other details
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
        Schema::dropIfExists('uninvoiced_rentals');
    }
};
