<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'message',
        'rating',
        'is_approved'
    ];
    
    protected $casts = [
        'is_approved' => 'boolean',
        'rating' => 'integer',
    ];
}
