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
        if (!$bookings->isEmpty()) {
            return $this->sendResponse($bookings, 'Booking retrieved successfully!');
        } else {
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
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start' => 'required|date_format:Y-m-d\TH:i:s',
            'line_user_id' => 'required',
            'phone_number' => 'required|string|max:15',
            'location' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Store data error', ['error' => 'Please check the input']);
        } else {
            try {
                $payload = $request->all();

//                if (!isset($payload['endDate'])) {
//                    $payload['endDate'] = $payload['startDate'];
//                    if (!isset($payload['endTime'])) {
//                        $startTime = strtotime($payload['startTime']);
//                        $adjustedEndTime = date('H:i', strtotime('+2 hours', $startTime));
//                        $payload['endTime'] = $adjustedEndTime;
//                    }
//                }

                $booking = Booking::create($payload);
                $success['info'] = $booking;

                $details = sprintf(
                    "Name: %s\nPhone: %s\nDateTime: %s\nLocation: %s",
                    $booking->title,
                    $booking->phone_number,
                    $booking->start,
                    $booking->location
                );

                $lineUserId = $request->input('line_user_id');
                $textMessage = "Thank you for your booking. We have successfully scheduled your booking.\n\nDetails:\n" . $details;
                LineBotController::sendPushMessage($lineUserId, $textMessage);

                return $this->sendResponse($success, 'Product stored successfully!');
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                return response()->json([
                    'message' => 'Something goes wrong while creating booking!! : ' . $e->getMessage()
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
            'start' => 'required',
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
