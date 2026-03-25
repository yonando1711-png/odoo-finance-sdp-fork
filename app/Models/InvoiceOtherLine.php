<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceOtherLine extends Model
{
    protected $fillable = [
        'invoice_other_id',
        'description',
        'quantity',
        'price_unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_unit' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(InvoiceOther::class, 'invoice_other_id');
    }
}
