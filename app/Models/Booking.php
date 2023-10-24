<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'title', 'startDate', 'startTime', 'endDate', 'endTime', 'line_user_id', 'phone_number', 'location'];
}
