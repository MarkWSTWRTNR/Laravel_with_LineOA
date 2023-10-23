<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\AccessToken;
use Google_Service_Calendar_Event;
use Illuminate\Http\Request;

use Google_Client;
use Google_Service_Calendar;

class GoogleCalendarController extends Controller
{
    //
    public function auth()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('client_secret.json'));
        $client->setRedirectUri(route('auth.google.callback'));
        $client->setScopes(Google_Service_Calendar::CALENDAR);

        return redirect($client->createAuthUrl());
    }


    public function authCallback(Request $request)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('client_secret.json'));
        $client->setRedirectUri(route('auth.google.callback'));
        $client->setScopes(Google_Service_Calendar::CALENDAR);

        if ($request->has('code')) {
            $client->fetchAccessTokenWithAuthCode($request->get('code'));
            $accessToken = $client->getAccessToken();

            AccessToken::create(['access_token' => json_encode($accessToken)]);

        }
    }


    public function createEvent(Booking $booking): string
    {
        $accessToken = AccessToken::latest()->first();

        if (!$accessToken) {
            return 'Token not found after authorization';
        }

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('client_secret.json'));
        $client->setAccessToken(json_decode($accessToken->access_token, true)); // โหลด Token จากฐานข้อมูล

        if ($client->isAccessTokenExpired()) {
            // หาก Token หมดอายุ คุณสามารถรีเฟรช Token ด้วยรหัสรีเฟรช หากคุณได้รับรหัสรีเฟรชจากผู้ใช้ในขั้นตอน OAuth
            // รีเฟรช Token และบันทึก Token ใหม่ลงในฐานข้อมูล
            $refreshedToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $accessToken->update(['access_token' => json_encode($refreshedToken)]);
        }

        $service = new Google_Service_Calendar($client);

        if ($client->isAccessTokenExpired()) {
            return 'Unable to refresh Token after authorization';
        }

        $event = new Google_Service_Calendar_Event(array(
            'summary' => 'Cleaning service',
            'location' => $booking->location,
            'description' => $booking->name . 'Contact: ' . $booking->phone_number,
            'start' => array(
                'dateTime' => $booking->bookDate . 'T' . $booking->bookTime,
                'timeZone' => 'Asia/Bangkok',
            ),
            'end' => array(
                'dateTime' => $booking->bookDate . 'T' . $booking->bookTime,
                'timeZone' => 'Asia/Bangkok',
            ),
        ));

        $calendarId = '95hv75tqqtcisjqi3dinhbqbok@group.calendar.google.com';
        $event = $service->events->insert($calendarId, $event);

        return 'Successfully scheduled your booking: ' . $event->htmlLink;
    }
}
