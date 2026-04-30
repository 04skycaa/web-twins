<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentOrderItem extends Model
{
    protected $table = 'payment_order_items';

    protected $fillable = [
        'payment_order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'subtotal',
        'discount_percent',
        'discount_amount',
        'final_price',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function paymentOrder(): BelongsTo
    {
        return $this->belongsTo(PaymentOrder::class, 'payment_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}
