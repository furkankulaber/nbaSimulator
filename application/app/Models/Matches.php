<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    use HasFactory;

    protected $table = 'matches';
    protected $fillable = ['match','home','away','home_score','away_score','minutes','week','status'];

    public function home(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Team::class,'id','home');
    }

    public function away(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Team::class,'id','away');
    }

    public function position()
    {
        return $this->hasMany(MatchesHistory::class,'match','id')->with('attacker','attacking_player','assist_player');
    }


}
