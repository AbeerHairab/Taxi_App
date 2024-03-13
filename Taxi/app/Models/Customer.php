<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = "customers";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'user_id',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    // public function bookOrAdds()
    // {
    //     return $this->hasMany(BookOrAdd::class,'customer_id');
    // }

    // public function complaints()
    // {
    //     return $this->hasMany(Complaint::class,'complaint_id');
    // }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class,'order_id');
    // }
}
