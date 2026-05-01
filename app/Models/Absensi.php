<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    public $timestamps = false;

    protected $fillable = ['user_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'status', 'keterangan', 'lokasi', 'created_at'];

    public function user() { return $this->belongsTo(User::class, 'user_id', 'uuid'); }
}
