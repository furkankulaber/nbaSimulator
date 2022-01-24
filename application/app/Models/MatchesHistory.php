<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchesHistory extends Model
{
    use HasFactory;

    protected $table = 'matches_history';
    protected $fillable = ['attacker','attacking_player','assist_player','attack_time','score'];


    public function attacking_player()
    {
     return $this->hasOne(Player::class,'id','attacking_player');
    }

    public function assist_player()
    {
        return $this->hasOne(Player::class,'id','assist_player');
    }

    public function attacker()
    {
        return $this->hasOne(Team::class,'id','attacker');
    }
}
