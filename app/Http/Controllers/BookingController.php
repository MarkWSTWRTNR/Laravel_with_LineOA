<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::all();
        if(!$bookings->isEmpty()) {
            return $this->sendResponse($bookings, 'Booking retrieved successfully!');
        }
        else {
            return $this->sendError('Retrieved data error.', ['error' => 'No data']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'line_user_id' => 'required',
            'phone_number' => 'required',
            'bookDate' => 'required|date',
            'bookTime' => 'required',
            'location' => 'required',
        ]);

//        dd($request->all());
        if ($validator->fails()) {
            return $this->sendError('Store data error', ['error' => 'Please check the input']);
        }
        else {
            try {
                $payload = $request->all();
                $booking = Booking::create($payload);
                $success['info'] = $booking;

                return $this->sendResponse($success, 'Product stored successfully!');
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                return response()->json([
                    'message' => 'Something goes wrong while createing booking!!'
                ], 500);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        //
        $request->validate([
            'bookDate' => 'required|date',
            'bookTime' => 'required',
            'location' => 'required',
        ]);

        try {
            $booking->fill($request->post())->update();
            $booking->save();

            return response()->json([
                'message' => 'Booking updated successfully!!'
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something goes wrong while updating booking!!'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
        try {
            $booking->delete();

            return response()->json([
                'message' => 'Booking deleted successfully!!'
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something goes wrong while deleting booking!!'
            ], 500);
        }
    }
}
