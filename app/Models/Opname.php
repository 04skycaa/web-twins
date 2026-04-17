<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Opname extends Model
{
    use HasUuids;

    protected $table = 'opname';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'tanggal',
        'store_id',
        'user_id'
    ];

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function details()
    {
        return $this->hasMany(OpnameDetail::class, 'opname_id', 'uuid');
    }

    public function getTotalItemsAttribute()
    {
        return $this->details->count();
    }

    public function getItemsFilledAttribute()
    {
        return $this->details->where('stok_fisik', '>', 0)->count();
    }

    public function getTotalSelisihAttribute()
    {
        return $this->details->sum('selisih');
    }

    public function getStatusAttribute()
    {
        $total = $this->total_items;
        $filled = $this->items_filled;

        if ($total == 0 || $filled == 0) {
            return 'Draft';
        }

        if ($filled < $total) {
            return 'Proses';
        }

        return 'Selesai';
    }
}
