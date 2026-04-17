<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StockCard extends Model
{
    use HasUuids;

    protected $table = 'stock_card';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    // Table has created_at but no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'uuid', 'product_id', 'jmlh', 'keterangan', 'store_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }
}
