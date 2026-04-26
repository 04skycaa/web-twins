<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Promo extends Model
{
    use HasUuids;

    protected $table = 'promo';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nama_promo',
        'kode_promo',
        'tipe',
        'nilai',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'image_banner',
        'deskripsi'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promo_products', 'promo_id', 'product_id', 'uuid', 'uuid')
                    ->withPivot('nilai_diskon', 'tipe_diskon');
    }

    public function stores()
    {
        return $this->belongsToMany(Outlet::class, 'promo_store', 'promo_id', 'store_id', 'uuid', 'uuid');
    }
}
