<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "complaints";
    protected $primaryKey= "id";
    protected $timestamp = true;
    protected $fillable = [
        'customer_id',
        'content',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
}
