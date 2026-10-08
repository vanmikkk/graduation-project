<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Board;
use App\Models\Card;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'name', 'sort'];

    public function board(){
        return $this->belongsTo(Board::class);
    }

    public function cards(){
        return $this->HasMany(Card::class);
    }
}
