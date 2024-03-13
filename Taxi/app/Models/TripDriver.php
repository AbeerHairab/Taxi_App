<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDriver extends Model
{
    use HasFactory;
    protected $table = "trip_drivers";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'trip_id',
        'driver_id',
        'acceptOrReject',
        'visible'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class,'driver_id');
    }
    public function trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }

    
}
