<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Availability;
use App\AppointmentField;
use App\AppointmentBooking;
use App\AppointmentReminder;

class Appointment extends Model
{
    use \Spiritix\LadaCache\Database\LadaCacheTrait;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities()
    {
        return $this->hasMany(Availability::class);
    }

    public function appointmentfields()
    {
        return $this->hasMany(AppointmentField::class);
    }

    public function appointmentreminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function bookings()
    {
        return $this->hasMany(AppointmentBooking::class);
    }
}
