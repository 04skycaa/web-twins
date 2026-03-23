<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $table = 'stock_opnames';
    
    protected $fillable = [
        'product_id', 'stok_sistem', 'stok_fisik', 
        'selisih', 'petugas', 'keterangan', 'tanggal_cek'
    ];
}
