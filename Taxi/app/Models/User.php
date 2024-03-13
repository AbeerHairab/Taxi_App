<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        //'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class,'user_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class,'user_id');
    }

    public function admin()
    {
        return $this->hasOne(Admin::class,'user_id');
    }




    //edit.........................
    public function tripCustomers()
    {
        return $this->hasMany(TripCustomer::class,'customer_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class,'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'customer_id');
    }

    public function orderDrivers()
    {
        return $this->hasMany(OrderDriver::class,'driver_id');
    }

    public function tripDrivers()
    {
        return $this->hasMany(TripDriver::class,'driver_id');
    }
}
