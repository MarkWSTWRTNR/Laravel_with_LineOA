<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
class LineBotController extends Controller
{
    public function webhook(Request $request) {
        $events = $request->input('events');

        // Handle each event
        foreach ($events as $event) {
            if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
                $this->replyText($event['replyToken'], $event['message']['text']);
            }
        }
        return response()->json(['status' => 'success'], 200);
    }

    protected function replyText($replyToken, $text) {
        $client = new Client();
        $response = $client->post('https://api.line.me/v2/bot/message/reply', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('LINE_CHANNEL_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'replyToken' => $replyToken,
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
