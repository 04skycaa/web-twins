<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $table = 'operator';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'nama',
        'fitur'
    ];

    protected $casts = [
        'fitur' => 'array'
    ];

    /**
     * Check if the operator has a specific feature.
     * 
     * @param string $feature
     * @return bool
     */
    public function hasFeature($feature): bool
    {
        if (!$this->fitur) {
            return false;
        }

        if (in_array('all_access', $this->fitur) || $this->nama === 'Owner') {
            return true;
        }
        return in_array($feature, $this->fitur);
    }
}
