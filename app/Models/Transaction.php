<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    
    protected $table = 'orders';

    protected $primaryKey = 'idorder';

    public $timestamps = false;

    const CREATED_AT = 'tanggalorder';
    const UPDATED_AT = null; 

    protected $fillable = [
        'namapelanggan', 'grandtotal', 'bayar', 'kembalian', 
        'idkasir', 'idoutlet', 'metode_pembayaran', 'status', 
        'tanggalorder', 'notelp'
    ];
}