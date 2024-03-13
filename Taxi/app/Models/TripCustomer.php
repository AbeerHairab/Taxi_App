<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripCustomer extends Model
{
    use HasFactory;
    protected $table = "trip_customers";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'customer_id',
        'trip_id',
        'bookOrAdd',
        'visible'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class,'trip_id');
    }

}
