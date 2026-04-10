<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintLog extends Model
{
    protected $fillable = ['invoice_name', 'print_count', 'kuitansi_print_count', 'kuitansi_pembayaran'];
}
