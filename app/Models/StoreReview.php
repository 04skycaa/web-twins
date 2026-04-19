<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreReview extends Model
{
    use HasUuids;

    protected $table = 'store_reviews';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; 

    protected $fillable = [
        'store_id',
        'user_id',
        'rating',
        'comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }
}
