<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'trip_id',
        'from_station_id',
        'to_station_id',
        'seat_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function fromStation()
    {
        return $this->belongsTo(Station::class, 'from_station_id');
    }

    public function toStation()
    {
        return $this->belongsTo(Station::class, 'to_station_id');
    }
}

