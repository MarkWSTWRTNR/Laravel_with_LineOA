<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\BaseController as BaseController;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

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
        } else {
            try {
                $payload = $request->all();
                $booking = Booking::create($payload);
                $success['info'] = $booking;

                $details = sprintf(
                    "Name: %s\nPhone: %s\nDate: %s\nTime: %s\nLocation: %s",
                    $booking->name,
                    $booking->phone_number,
                    $booking->bookDate,
                    $booking->bookTime,
                    $booking->location
                );

                $event = $this->createGoogleCalendarEvent($booking);

                echo 'Event created: ' . $event->getId();

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

    private function createGoogleCalendarEvent(Booking $booking): Google_Service_Calendar_Event
    {
        try {
            $client = new Google_Client();
            $client->setAuthConfig('C:\Users\user00\Laravel_with_LineOA\client_secret.json');
            $client->setScopes(Google_Service_Calendar::CALENDAR);

            $service = new Google_Service_Calendar($client);

            $event = new Google_Service_Calendar_Event(array(
                'summary' => 'Cleaning service',
                'location' => $booking->location,
                'description' => 'House ' . $booking->name . 'Contact: ' . $booking->phone_number,
                'start' => array(
                    'dateTime' => $booking->bookDate . 'T' . $booking->bookTime,
                    'timeZone' => 'Asia/Bangkok',
                ),
                'end' => array(
                    'dateTime' => $booking->bookDate . 'T' . $booking->bookTime,
                    'timeZone' => 'Asia/Bangkok',
                ),
            ));

            $calendarId = 'Booking';
            $event = $service->events->insert($calendarId, $event);

            return $event;
        } catch (\Exception $e) {
            \Log::error('Error creating Google Calendar event: ' . $e->getMessage());
            throw new \Exception('Error creating Google Calendar event: ' . $e->getMessage());
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
