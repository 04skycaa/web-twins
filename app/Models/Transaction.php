<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    
    protected $table = 'transactions';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'uuid', 'diskon', 'total', 'bayar', 'kembalian', 
        'metode_pembayaran', 'tanggal', 'jenis', 'store_id', 
        'is_recent_sync', 'pengiriman', 'pajak', 'user_id', 
        'shift_id', 'contact_id', 'catatan', 'tujuan_store_id', 'status'
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function tujuanStore()
    {
        return $this->belongsTo(Outlet::class, 'tujuan_store_id', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'uuid');
    }
}