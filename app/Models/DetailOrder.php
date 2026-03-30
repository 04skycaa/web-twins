<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailOrder extends Model
{
    protected $table = 'detailorder';

    protected $primaryKey = 'iddetail';

    public $timestamps = false;

    protected $fillable = [
        'idorder',
        'idproduk',
        'harga',
        'jumlah',
        'subtotal'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'idorder', 'idorder');
    }
}