<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    protected $table = 'outlet';
    protected $primaryKey = 'id';
    const UPDATED_AT = null;

    protected $fillable = ['name', 'address', 'created_at'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'outlet_id', 'id');
    }
}