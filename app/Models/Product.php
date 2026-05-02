<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasUuids;

    protected $table = 'products';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'barcode',
        'nama_produk',
        'kategori_id',
        'harga_modal',
        'harga_jual',
        'image_url'
    ];

    protected $appends = ['resolved_image_url'];

    public function getResolvedImageUrlAttribute()
    {
        $path = $this->image_url;
        if (!$path) {
            return asset('images/placeholder-product.png');
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($cleanPath);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'kategori_id', 'uuid');
    }

    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'product_id', 'uuid');
    }

    public function stores()
    {
        return $this->hasMany(ProductStore::class, 'product_id', 'uuid');
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_products', 'product_id', 'promo_id');
    }

    public function priceLevels()
    {
        return $this->hasMany(PriceLevel::class, 'product_id', 'uuid')->orderBy('jmlh', 'asc');
    }
}