<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory;
    protected $fillable = ['plate_number'];

   

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }
}

