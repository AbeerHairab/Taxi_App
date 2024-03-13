<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDriver extends Model
{
    use HasFactory;
    protected $table = "order_drivers";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'order_id',
        'driver_id',
        'acceptOrReject',
        'visible'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class,'driver_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }    
}
