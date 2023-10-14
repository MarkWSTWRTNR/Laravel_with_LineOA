<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LineBotController;
use App\Models\Booking;

class SendBookingReminders extends Command
{
    protected $signature = 'send:booking-reminders';
    protected $description = 'Send booking reminders to users';

    public function handle()
    {
        $bookings = Booking::whereDate('bookDate', now()->addDay())->get();

        foreach ($bookings as $booking) {
            $textMessage = "Reminder! You have a booking scheduled for tomorrow.";
            app(LineBotController::class)->sendPushMessage($booking->line_user_id, $textMessage);
        }
    }
}
