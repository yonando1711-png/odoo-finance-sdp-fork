<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDp extends Model
{
    protected $fillable = [
        'odoo_id',
        'name',
        'invoice_date',
        'invoice_date_due',
        'payment_term',
        'partner_name',
        'partner_npwp',
        'partner_address',
        'invoice_pic',
        'reserved_lot',
        'ref',
        'journal_name',
        'payment_state',
        'state',
        'amount_untaxed',
        'amount_tax',
        'amount_total',
        'partner_bank',
        'bc_manager',
        'bc_spv',
        'narration',
        'contract_ref',
        'print_count',
        'last_printed_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'invoice_date_due' => 'date',
        'last_printed_at' => 'datetime',
        'amount_untaxed' => 'decimal:2',
        'amount_tax' => 'decimal:2',
        'amount_total' => 'decimal:2',
    ];

    public function lines()
    {
        return $this->hasMany(InvoiceDpLine::class);
    }

    public function getPaymentDescriptionAttribute(): string
    {
        return 'Uang Muka Nopol ' . ($this->reserved_lot ? trim($this->reserved_lot) : '');
    }
}
