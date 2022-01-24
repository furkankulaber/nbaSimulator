<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['abbreviation','conference','full_name'];

    public function players()
    {
        return $this->hasMany(Player::class);
    }
}
