<?php

namespace App\Http\Controllers;


use App\Models\Matches;
use App\Services\GameSimulator;
use App\Services\MatchGenerator;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{

    public function home()
    {
        $matchGenerator = new MatchGenerator();
        //$matchGenerator->generataMatch();

        $response = Matches::whereIn('status',['s','w'])->groupBy('week')->first()->all();
        $gameSimulator = new GameSimulator();
        $gameSimulator->simulateWeek($response);exit();
    }
}
