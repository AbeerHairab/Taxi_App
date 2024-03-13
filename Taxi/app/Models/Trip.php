<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trip extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "trips";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'from',
        'to',
        'NumOfSeats',
        'accepted',
        'rejected',
        'status',
        'paid',
        'bookedSeats',
        'availableSeats',
        'startTime',
        'endTime',
        'estimatedCost',
        'estimatedDuration',
        'realCost',
        'realDuration'
    ];

    public function tripCustomer()
    {
        return $this->hasMany(TripCustomer::class,'trip_id');
    }

    public function tripDrivers()
    {
         return $this->hasMany(TripDriver::class,'trip_id');
    }
}
