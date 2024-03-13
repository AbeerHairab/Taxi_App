<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "cars";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'number',
        'photo',
        'type',
        'truckOrTaxi'
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class,'car_id');
    }

}
