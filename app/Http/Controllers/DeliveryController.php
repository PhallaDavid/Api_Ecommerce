<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Events\DeliveryLocationUpdated;

class DeliveryController extends Controller
{
    /**
     * Update delivery location and broadcast.
     */
    public function updateLocation(Request $request, $orderId)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Save to database (optional but recommended)
        $order = Order::findOrFail($orderId);
        $order->delivery_latitude = $latitude;
        $order->delivery_longitude = $longitude;
        $order->save();

        // Broadcast the updated location
        event(new DeliveryLocationUpdated($orderId, $latitude, $longitude));

        return response()->json([
            'status' => 'success',
            'orderId' => $orderId,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }

    /**
     * Get the last known delivery location.
     */
    public function getLocation($orderId)
    {
        $order = Order::findOrFail($orderId);

        return response()->json([
            'orderId'   => $order->id,
            'latitude'  => $order->delivery_latitude,
            'longitude' => $order->delivery_longitude,
            'status'    => 'success'
        ]);
    }
}
