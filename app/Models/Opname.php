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
        'user_id',
        'status',
        'kategori_id'
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

    public function getTotalSelisihAttribute()
    {
        return $this->details->sum('selisih');
    }

    public function getTotalKerugianAttribute()
    {
        // Kerugian = Selisih (jika negatif) x Harga Modal
        return $this->details->sum(function($detail) {
            // Jika selisih negatif (stok hilang), maka itu kerugian (nilai negatif)
            if ($detail->selisih < 0) {
                $modal = $detail->product ? ($detail->product->harga_modal ?? 0) : 0;
                return $detail->selisih * $modal;
            }
            return 0;
        });
    }
}
