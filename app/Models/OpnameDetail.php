<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OpnameDetail extends Model
{
    use HasUuids;

    protected $table = 'opname_detail';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'opname_id',
        'product_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan'
    ];

    public function opname()
    {
        return $this->belongsTo(Opname::class, 'opname_id', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}
