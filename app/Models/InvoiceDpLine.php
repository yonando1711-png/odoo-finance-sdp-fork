<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDpLine extends Model
{
    protected $fillable = [
        'invoice_dp_id',
        'odoo_line_id',
        'description',
        'quantity',
        'price_unit',
        'amount',
        'uom',
        'product_name',
        'serial_number',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_unit' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(InvoiceDp::class, 'invoice_dp_id');
    }
}
