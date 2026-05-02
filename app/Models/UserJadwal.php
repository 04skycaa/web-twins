<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserJadwal extends Model
{
    use HasFactory;

    protected $table = 'user_jadwal';
    public $timestamps = false;

    protected $fillable = ['user_id', 'shift_uuid', 'tanggal'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'uuid'); }
    public function shift() { return $this->belongsTo(Shift::class, 'shift_uuid', 'uuid'); }
}
