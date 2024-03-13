<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $table = "drivers";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'car_id',
        'user_id',
        'photo',
        'availability',
        'drivingCertificate'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function car()
    {
        return $this->belongsTo(Car::class,'car_id');
    }

    // public function trips()
    // {
    //     return $this->hasMany(Trip::class,'driver_id');
    // }

     public function orderDrivers()
     {
         return $this->hasMany(OrderDriver::class,'driver_id');
     }
}
