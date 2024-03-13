<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "orders";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'customer_id',
        'from',
        'to',
        'accepted',
        'paid',
        'status',
        'estimatedCost',
        'estimatedDuration',
        'realCost',
        'realDuration',
        'visible'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }

    public function orderDriver()
    {
         return $this->hasMany(OrderDriver::class,'order_id');
    }
}
