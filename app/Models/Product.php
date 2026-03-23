<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products'; 
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nama_produk', 'sku', 'kategori', 'harga_beli', 
        'harga_jual', 'stok', 'minimal_stok', 'lokasi_rak', 'satuan'
    ];
}