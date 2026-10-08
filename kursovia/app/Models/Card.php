<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Column;
use App\Models\User;

class Card extends Model
{
    use HasFactory;

    protected $fillable = ['column_id', 'user_id', 'name', 'description', 'date', 'sort'];

    public function column(){
        return $this->belongsTo(Column::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
