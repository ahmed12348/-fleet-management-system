<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    protected $fillable = ['plate_number'];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }
}

