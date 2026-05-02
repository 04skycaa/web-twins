<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionItem extends Model
{
    use HasUuids;

    protected $table = 'transaction_items';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'transaction_id', 'product_id', 'qty', 'harga_beli', 'harga_jual_baru', 'kadaluarsa'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'uuid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }
}
