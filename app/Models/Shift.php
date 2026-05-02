<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['uuid', 'store_id', 'nama', 'waktu_mulai', 'waktu_selesai'];

    public function store() { return $this->belongsTo(Outlet::class, 'store_id', 'uuid'); }
}
