<?php

namespace App\Console\Commands;
namespace App\Http\Controllers;

use Illuminate\Console\Command;
use App\Models\Booking;
use Illuminate\Support\Carbon;

class BookingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:booking-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind customer & cleaner just before their cleanning day';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $bookings = Booking::where('start', '<=', Carbon::now()->add(1, 'day')->toDateTimeString())
            ->where('start', '>', Carbon::now()->toDateTimeString())
            ->where('notified', 0)
            ->get();

        foreach ($bookings as $booking) {
            $details = sprintf(
                "Name: %s\nPhone: %s\nDateTime: %s\nLocation: %s",
                $booking->title,
                $booking->phone_number,
                $booking->start,
                $booking->location
            );

            $lineUserId = $booking->line_user_id;
            $textMessage = "Tomorrow we have a cleaning.\n\nDetails:\n" . $details;
            LineBotController::sendPushMessage($lineUserId, $textMessage);

            $booking->notified = 1;
            $booking->save();
        }
    }
}
