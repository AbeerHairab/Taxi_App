<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $table = "admins";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'user_id',
        'photo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
