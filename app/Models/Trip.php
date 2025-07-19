<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = ['name', 'bus_id'];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function tripStations(): HasMany
    {
        return $this->hasMany(TripStation::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function stations()
    {
        return $this->belongsToMany(\App\Models\Station::class, 'trip_stations')
                    ->withPivot('stop_order');
    }
    
}

