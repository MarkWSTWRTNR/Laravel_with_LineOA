<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Log;

class LineBotController extends Controller
{
    public function webhook(Request $request)
    {
        $events = $request->input('events');

        foreach ($events as $event) {
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
                $this->replyText($event['replyToken'], $event['message']['text']);
            } elseif ($event['type'] == 'follow') {  // New friend added
                $displayName = $this->getDisplayName($event['source']['userId']);
                $this->replyWithGreetingAndServiceInfo($event['replyToken'], $displayName);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function replyText($replyToken, $text)
    {
        $client = new Client();
        $response = $client->post('https://api.line.me/v2/bot/message/reply', ['headers' => ['Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'), 'Content-Type' => 'application/json',], 'json' => ['replyToken' => $replyToken, 'messages' => [['type' => 'text', 'text' => $text,],],],]);

        return $response->getBody();
    }

    protected function getDisplayName($userId)
    {
        $client = new Client();
        $response = $client->get('https://api.line.me/v2/bot/profile/' . $userId, ['headers' => ['Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),],]);
        $body = json_decode($response->getBody(), true);
        return $body['displayName'] ?? 'user';
    }

    protected function replyWithGreetingAndServiceInfo($replyToken, $displayName)
    {
        $client = new Client();

        // Greeting message
        $greetingMessage = ['type' => 'text', 'text' => "Hi, " . $displayName,];

        // Text message for cleaning service details
        $textMessage = ['type' => 'text', 'text' => "Welcome to Atom Cleaning Services!\n\n About us:Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\n\n If you are interested in booking with us, click on the calendar in our services below. \nService rate: $50/hour",];

        // Image message for cleaning service
        $imageMessage = ['type' => 'image', 'originalContentUrl' => 'https://img.freepik.com/free-vector/surface-cleaning-equipment_23-2148530194.jpg?w=740&t=st=1697041124~exp=1697041724~hmac=d40611fc282c347f755dd10908036217db1df2e1091863a484897ecddf59ed51', 'previewImageUrl' => 'https://img.freepik.com/free-vector/cleaners-with-cleaning-products-housekeeping-service_18591-52068.jpg?w=740&t=st=1697041146~exp=1697041746~hmac=9576d8a76b5f094e0cde5da237e75b52cd2031f731af013200c099930c9c33e5',];

        try {
            $response = $client->post('https://api.line.me/v2/bot/message/reply', ['headers' => ['Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'), 'Content-Type' => 'application/json',], 'json' => ['replyToken' => $replyToken, 'messages' => [$greetingMessage, $textMessage, $imageMessage],],]);

            return $response->getBody();
        } catch (Exception $e) {
            // Here you can log the error or handle it as per your needs
            Log::error('Error sending message: ' . $e->getMessage());
        }
    }
    public static function sendPushMessage($userId, $text)
    {
        $client = new Client();
        $response = $client->post('https://api.line.me/v2/bot/message/push', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'to' => $userId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text,
                    ],
                ],
            ],
        ]);

        return $response->getBody();
    }
}
