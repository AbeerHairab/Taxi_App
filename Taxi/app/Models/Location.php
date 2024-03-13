<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = "locations";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'name',
        'altitude',
        'longitude',
    ];
}
