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
        $fitur = $this->fitur;

        // Ensure $fitur is an array even if casting failed or data is raw string
        if (is_string($fitur)) {
            $fitur = json_decode($fitur, true) ?: [];
        }

        if (!$fitur || !is_array($fitur)) {
            return false;
        }

        if (in_array('all_access', $fitur) || strtolower($this->nama) === 'owner') {
            return true;
        }
        return in_array((string)$feature, $fitur);
    }
}
