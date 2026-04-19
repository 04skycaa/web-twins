<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Outlet extends Model
{
    use HasUuids;

    protected $table = 'store';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';

    protected $fillable = ['nama', 'alamat', 'notelp', 'status_aktif', 'jam_buka', 'rating'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'store_id', 'uuid');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StoreReview::class, 'store_id', 'uuid');
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class, 'promo_store', 'store_id', 'promo_id');
    }
}