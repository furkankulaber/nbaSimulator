<?php

namespace App\Http\Controllers;

use App\Models\Matches;
use App\Models\MatchesHistory;
use App\Services\GameSimulator;
use App\Services\MatchGenerator;

class ApiController extends Controller
{
    private $week = 0;

    public function getRunningWeekData()
    {
        $response = Matches::whereIn('status', ['s', 'f'])->with(['home', 'away', 'position'])->get()->groupBy('week');
        $return = [];
        foreach ($response as $key => $week) {
            foreach ($week as $match) {
                $match->pos = $this->stats($match);
                $return[$key][] = $match;
            }
        }

        return response()->json($return);
    }

    public function createMatch()
    {
        $matchGenerator = new MatchGenerator();
        $response = $matchGenerator->generataMatch();

        return response()->json(['status' => $response]);
    }

    public function simulateGame()
    {
        $response = Matches::whereIn('status', ['s', 'w'])->get()->groupBy('week')->first();
        $gameSimulator = new GameSimulator();
        $gameSimulator->simulateWeek($response);


        return $this->getRunningWeekData();
    }

    /** Tüm haftaların işlenmesi için kod yazıldı fakat şu andaki işleme mantığı ile yeterli hızda işlem yapamaz, bulk işlem yapılmalı */
    //public function allTimeSimulate()
    //{
    //    $this->createMatch();
    //    $response = Matches::whereIn('status', ['s', 'w'])->get()->groupBy('week');
    //    $gameSimulator = new GameSimulator();
    //    $i = 0;
    //    $ii = 0;
    //    foreach ($response as $week) {
    //        foreach ($week as $game) {
    //            if (in_array($game->status, ['w', 's'])) {
    //                $gameSimulator->simulateWeek($week, true, false);
    //            }
    //        }
    //    }
                                                                                                            //
    //    return $this->getRunningWeekData();
    //}

    public function getMatches()
    {
        $response = Matches::whereIn('status', ['w', 's', 'f'])->with(['home', 'away', 'position'])->get()->groupBy('week');
        $return = [];
        foreach ($response as $key => $week) {
            foreach ($week as $match) {
                $match->pos = $this->stats($match);
                $return[$key][] = $match;
            }
        }


        return response()->json($return);
    }

    private function stats($data)
    {
        $return = [
            'player' => []
        ];
        foreach ($data->position as $pos) {
            if (isset($return['player'][$pos->attacking_player]['attack'])) {
                $return['player'][$pos->attacking_player]['attack']++;
            } else {
                $return['player'][$pos->attacking_player]['attack'] = 1;
            }
            if (isset($return['player'][$pos->assist_player]['assist'])) {
                if($pos->score > 0) {
                    $return['player'][$pos->assist_player]['assist']++;
                }
            } else {
                if($pos->score > 0) {
                    $return['player'][$pos->assist_player]['assist'] = 1;
                }
            }
            if (isset($return['player'][$pos->attacking_player]['b_' . $pos->score])) {
                $return['player'][$pos->attacking_player]['b_' . $pos->score]++;
            } else {
                $return['player'][$pos->attacking_player]['b_' . $pos->score] = 1;
            }
            if (!isset($return['player'][$pos->attacking_player]['name'])) {
                $return['player'][$pos->attacking_player]['name'] = $pos->getRelations()['attacking_player']->first_name . ' ' . $pos->getRelations()['attacking_player']->last_name;
            }
            if (!isset($return['player'][$pos->assist_player]['name'])) {
                $return['player'][$pos->assist_player]['name'] = $pos->getRelations()['assist_player']->first_name . ' ' . $pos->getRelations()['assist_player']->last_name;
            }
            if (!isset($return['player'][$pos->attacking_player]['team'])) {
                $return['player'][$pos->attacking_player]['team'] = $pos->getRelations()['attacking_player']->team;
            }
            if (!isset($return['player'][$pos->assist_player]['team'])) {
                $return['player'][$pos->assist_player]['team'] = $pos->getRelations()['assist_player']->team;
            }

        }
        return $return;
    }
}
