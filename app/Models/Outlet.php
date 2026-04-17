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

    protected $fillable = ['nama', 'alamat'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'store_id', 'uuid');
    }
}