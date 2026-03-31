<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    protected $table = 'outlet';
    protected $primaryKey = 'idoutlet';
    const UPDATED_AT = null;

    protected $fillable = ['kode_outlet', 'nama_outlet', 'alamat', 'telepon', 'is_active'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'outlet_id', 'idoutlet');
    }
}