<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    
    protected $table = 'transactions';

    protected $primaryKey = 'idorder';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null; 

    protected $fillable = [
        'namapelanggan', 'grandtotal', 'bayar', 'kembalian', 
        'idkasir', 'idoutlet', 'metode_pembayaran', 'status', 
        'tanggalorder', 'notelp'
    ];
}