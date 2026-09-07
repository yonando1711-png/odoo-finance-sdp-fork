<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InvoiceSubscription extends Model
{
    protected $fillable = [
        'period_odoo_id',
        'period_numeric_id',
        'so_name',
        'partner_name',
        'rental_status',
        'rental_type',
        'actual_start_rental',
        'actual_end_rental',
        'period_type',
        'product_name',
        'license_plate',
        'period_start',
        'period_end',
        'invoice_date',
        'due_date',
        'payment_date',
        'price_unit',
        'duration_price',
        'rental_uom',
        'invoice_name',
        'invoice_ref',
        'customer_ref',
        'transaction_code',
        'invoice_state',
        'payment_state',
        'invoice_amount',
        'partner_npwp',
        'partner_address',
        'partner_address_complete',
        'synced_at',
        'invoice_pic',
        'amount_paid',
        'journal_code',
        'journal_name',
    ];

    protected $casts = [
        'actual_start_rental' => 'datetime',
        'actual_end_rental'   => 'datetime',
        'period_start'        => 'date',
        'period_end'          => 'date',
        'invoice_date'        => 'date',
        'due_date'            => 'date',
        'payment_date'        => 'date',
        'price_unit'          => 'decimal:2',
        'duration_price'      => 'decimal:2',
        'invoice_amount'      => 'decimal:2',
        'amount_paid'         => 'decimal:2',
        'synced_at'           => 'datetime',
    ];

    // ─── Computed status for display ───

    /**
     * Get the display status: not_invoiced | draft | paid | unpaid
     */
    public function getStatusAttribute(): string
    {
        if (empty($this->invoice_name) && empty($this->invoice_state)) {
            return 'not_invoiced';
        }
        if (strtolower($this->invoice_state ?? '') === 'draft') {
            return 'draft';
        }
        $pay = strtolower($this->payment_state ?? '');
        if (in_array($pay, ['partial', 'partially paid', 'partially_paid'])) {
            return 'partial';
        }
        if (in_array($pay, ['paid', 'in_payment', 'reversed'])) {
            return $pay;
        }
        if (strtolower($this->invoice_state ?? '') === 'posted') {
            return 'unpaid';
        }
        // catch-all: has something but doesn't match known states
        return 'draft';
    }

    /**
     * Whether the invoice date has already passed (overdue, not yet invoiced)
     */
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->invoice_date || $this->status !== 'not_invoiced') {
            return false;
        }
        return \Carbon\Carbon::parse($this->invoice_date)->lt(\Carbon\Carbon::today());
    }

    /**
     * Get the number of days the invoice is overdue
     */
    public function getOverDueDaysAttribute(): int
    {
        if (in_array($this->status, ['paid', 'in_payment'])) {
            return 0;
        }
        
        $targetDateStr = $this->due_date ?: $this->invoice_date;
        if (!$targetDateStr) {
            return 0;
        }
        
        $targetDate = \Carbon\Carbon::parse($targetDateStr);
        $today = \Carbon\Carbon::today();
        
        if ($today->gt($targetDate)) {
            return (int) abs($today->diffInDays($targetDate));
        }
        
        return 0;
    }

    // ─── Scopes ───

    public function scopeStatus(Builder $q, string $status): Builder
    {
        return match($status) {
            'not_invoiced' => $q->whereNull('invoice_name')->orWhere('invoice_name', ''),
            'draft'        => $q->whereRaw("LOWER(invoice_state) = 'draft'"),
            'paid'         => $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(payment_state)'), ['paid', 'in_payment', 'reversed']),
            'partial'      => $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(payment_state)'), ['partial', 'partially paid', 'partially_paid']),
            'unpaid'       => $q->whereRaw("LOWER(invoice_state) = 'posted'")->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(payment_state)'), ['paid', 'in_payment', 'partial', 'partially paid', 'partially_paid', 'reversed']),
            default        => $q,
        };
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($inner) use ($term) {
            $inner->where('so_name', 'like', "%{$term}%")
                  ->orWhere('partner_name', 'like', "%{$term}%")
                  ->orWhere('invoice_name', 'like', "%{$term}%")
                  ->orWhere('product_name', 'like', "%{$term}%");
        });
    }

    public function scopeJournal(Builder $q, string $journal): Builder
    {
        if ($journal === 'all' || empty($journal)) {
            return $q;
        }
        return $q->where('journal_code', $journal);
    }

    /**
     * Get journal badge details for display
     */
    public function getJournalBadgeAttribute(): array
    {
        return match($this->journal_code) {
            'INVOW' => ['label' => 'INVOW', 'title' => 'Other wo Tax (Own Risk)', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 border border-purple-300 dark:border-purple-700/50'],
            'INVOT' => ['label' => 'INVOT', 'title' => 'Other with Tax (ETLE/Toolkit/Ongkir)', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-300 dark:border-amber-700/50'],
            'INVRT' => ['label' => 'INVRT', 'title' => 'Sewa Retail', 'class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400 border border-teal-300 dark:border-teal-700/50'],
            'INVDV' => ['label' => 'INVDV', 'title' => 'Invoice Driver', 'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-700/50'],
            default => ['label' => 'INVRS', 'title' => 'Sewa Subscription', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-300 dark:border-blue-700/50'],
        };
    }

    /**
     * Get product_name without brackets [ ]
     */
    public function getCleanProductNameAttribute(): string
    {
        return str_replace(['[', ']'], '', $this->product_name ?? '');
    }

    /**
     * Get so_name without brackets [ ]
     */
    public function getCleanSoNameAttribute(): string
    {
        return str_replace(['[', ']'], '', $this->so_name ?? '');
    }
}
