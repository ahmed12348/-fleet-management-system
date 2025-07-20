<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function tripStations(): HasMany
    {
        return $this->hasMany(TripStation::class);
    }

    public function bookingsFrom(): HasMany
    {
        return $this->hasMany(Booking::class, 'from_station_id');
    }

    public function bookingsTo(): HasMany
    {
        return $this->hasMany(Booking::class, 'to_station_id');
    }
}
