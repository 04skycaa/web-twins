<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $table = 'stock_requests';

    protected $fillable = [
        'product_id', 'jumlah_minta', 'prioritas', 
        'status', 'pemohon', 'alasan_permintaan'
    ];
}