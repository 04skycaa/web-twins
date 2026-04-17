<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements MustVerifyEmail
{
    
    use HasFactory, Notifiable, HasUuids;


    protected $primaryKey = 'uuid';
    protected $keyType = 'string';


    protected $fillable = [
        'uuid',
        'username', // Kita mapping 'name' ke sini
        'no_hp', 
        'email', 
        'password', 
        'operator_id', // Kita mapping 'role' ke sini
        'store_id',    // Kita mapping 'outlet_id' ke sini
        'status_aktif'
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status_aktif' => 'boolean'
    ];

    // MAPPING name -> username
    public function getNameAttribute()
    {
        return $this->username;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    // MAPPING role -> operator relation
    public function getRoleAttribute()
    {
        if ($this->operator) {
            return str_replace(' ', '_', strtolower($this->operator->nama));
        }
        return 'user';
    }

    public function setRoleAttribute($value)
    {
        // Convert 'owner' or 'Owner' or 'kepala_toko' back to DB format
        $nama = str_replace('_', ' ', ucwords($value, '_'));
        $operator = Operator::where('nama', $nama)->first();
        if ($operator) {
            $this->attributes['operator_id'] = $operator->uuid;
        }
    }



    // MAPPING outlet_id -> store_id
    public function getOutletIdAttribute()
    {
        return $this->store_id;
    }

    public function setOutletIdAttribute($value)
    {
        $this->attributes['store_id'] = $value;
    }

    public function getAuthIdentifierName()
    {
        return 'uuid';
    }


    public function isOwner(): bool
    {
        return in_array($this->role, ['Owner', 'owner']);
    }

    public function isKepalaToko(): bool
    {
        return in_array($this->role, ['Kepala Toko', 'kepala_toko']);
    }


    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function outlet()
    {
        return $this->store();
    }

}
