<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $tomorrow = now()->addDay();
            $bookings = Booking::whereDate('bookDate', $tomorrow)->get();

            foreach ($bookings as $booking) {
                $lineUserId = $booking->line_user_id;
                $textMessage = "Reminder: You have a booking scheduled for tomorrow.";
                LineBotController::sendPushMessage($lineUserId, $textMessage);
            }
        })->daily();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}
