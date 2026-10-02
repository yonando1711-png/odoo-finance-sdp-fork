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

    public function getLotsAttribute(): array
    {
        $lots = [];
        if (!empty($this->reserved_lot)) {
            $parts = preg_split('/[,\n\/]+/', $this->reserved_lot);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (!empty($trimmed) && !in_array($trimmed, $lots)) {
                    $lots[] = $trimmed;
                }
            }
        }
        if ($this->relationLoaded('lines')) {
            foreach ($this->lines as $line) {
                if (!empty($line->serial_number)) {
                    $trimmed = trim($line->serial_number);
                    if (!empty($trimmed) && !in_array($trimmed, $lots)) {
                        $lots[] = $trimmed;
                    }
                }
            }
        }
        return $lots;
    }

    public function getPaymentDescriptionAttribute(): string
    {
        return 'Uang Muka Nopol ' . ($this->reserved_lot ? trim($this->reserved_lot) : '');
    }
}
