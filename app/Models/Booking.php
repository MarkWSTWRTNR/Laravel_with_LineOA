<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'line_user_id', 'phone_number', 'bookDate', 'bookTime', 'location'];
}
