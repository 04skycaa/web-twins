<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionDetail extends Model
{
    use HasUuids;

    protected $table = 'transaction_detail';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'transaction_id', 'product_id', 'jmlh', 'harga_modal', 'harga_jual'
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
