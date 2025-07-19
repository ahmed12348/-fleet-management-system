<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
public function availableSeats(Request $request)
{
    $request->validate([
        'trip_id' => 'required|exists:trips,id',
        'from_station_id' => 'required|exists:stations,id',
        'to_station_id' => 'required|exists:stations,id',
    ]);

    $tripId = $request->trip_id;

    $tripStations = \DB::table('trip_stations')
        ->where('trip_id', $tripId)
        ->pluck('stop_order', 'station_id');

    $fromOrder = $tripStations[$request->from_station_id] ?? null;
    $toOrder = $tripStations[$request->to_station_id] ?? null;

    if (!$fromOrder || !$toOrder || $fromOrder >= $toOrder) {
        return response()->json(['error' => 'Invalid station order.'], 422);
    }

    $allSeats = range(1, 12);

    $conflictingSeatNumbers = Booking::where('trip_id', $tripId)
        ->get()
        ->filter(function ($booking) use ($tripStations, $fromOrder, $toOrder) {
            $existingFrom = $tripStations[$booking->from_station_id] ?? null;
            $existingTo = $tripStations[$booking->to_station_id] ?? null;

            return $existingFrom < $toOrder && $existingTo > $fromOrder;
        })
        ->pluck('seat_number')
        ->unique()
        ->toArray();

    $availableSeats = array_values(array_diff($allSeats, $conflictingSeatNumbers));

    return response()->json(['available_seats' => $availableSeats]);
}


    public function book(Request $request)
    {
        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'from_station_id' => 'required|exists:stations,id',
            'to_station_id' => 'required|exists:stations,id',
            'seat_number' => 'required|integer|min:1|max:12',
        ]);

        $user = $request->user();
        $trip = Trip::with('stations')->findOrFail($request->trip_id);
        $fromStation = $trip->stations->firstWhere('id', $request->from_station_id);
        $toStation = $trip->stations->firstWhere('id', $request->to_station_id);

     
        if (!$fromStation || !$toStation) {
            return response()->json(['error' => 'One or both stations do not exist in this trip.'], 422);
        }
   
        $fromOrder = $fromStation->pivot->stop_order;
        $toOrder = $toStation->pivot->stop_order;

        if ($fromOrder >= $toOrder) {
            return response()->json(['error' => 'Invalid station order.'], 422);
        }

        $tripStations = DB::table('trip_stations')
            ->where('trip_id', $trip->id)
            ->pluck('stop_order', 'station_id');

        $fromOrder = $tripStations[$request->from_station_id] ?? null;
        $toOrder = $tripStations[$request->to_station_id] ?? null;

        if (!$fromOrder || !$toOrder || $fromOrder >= $toOrder) {
            return response()->json(['error' => 'Invalid station order.'], 422);
        }

        // Check for seat conflicts (overlapping bookings)
        $conflictingSeat = Booking::where('trip_id', $trip->id)
            ->where('seat_number', $request->seat_number)
            ->get()
            ->filter(function ($booking) use ($tripStations, $fromOrder, $toOrder) {
                $existingFrom = $tripStations[$booking->from_station_id] ?? null;
                $existingTo = $tripStations[$booking->to_station_id] ?? null;

                // Check if there's an overlap
                return $existingFrom < $toOrder && $existingTo > $fromOrder;
            })
            ->isNotEmpty();

        if ($conflictingSeat) {
            return response()->json(['error' => 'This seat is already booked in the selected range.'], 409);
        }
    
        $booking = Booking::create([
            'user_id' => $user->id,
            'trip_id' => $trip->id,
            'from_station_id' => $request->from_station_id,
            'to_station_id' => $request->to_station_id,
            'seat_number' => $request->seat_number,
        ]);

        return response()->json([
            'message' => 'Seat successfully booked.',
            'booking' => $booking
        ]);
    }
}
