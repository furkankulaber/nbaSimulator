<?php

namespace App\Services;

use App\Models\Matches;
use App\Models\MatchesHistory;
use App\Models\Player;
use App\Models\Team;
use Carbon\Carbon;

class GameSimulator
{

    const ONE_MINUTE = 60;

    public function simulateWeek($weeklyGame, $allTime = false, $truncate = false)
    {
        if ($truncate === true) {
            MatchesHistory::truncate();
        }

        /** Tüm haftaların işlenmesi için kod yazıldı fakat şu andaki işleme mantığı ile yeterli hızda işlem yapamaz, bulk işlem yapılmalı */
        if ($allTime === true) {
            foreach ($weeklyGame as $game) {
                for ($i = 1; $i <= $_ENV['MAX_GAME_MINUTES']; $i++) {
                    if (in_array($game->status, ['w', 's'])) {
                        $insertData = $this->matchHandler($game);
                    }else{
                        continue;
                    }
                }
            }

        } else {
            foreach ($weeklyGame as $game) {
                if (in_array($game->status, ['w', 's'])) {
                    $insertData = $this->matchHandler($game);
                }
            }
        }


    }

    private function matchHandler(Matches $gameO)
    {
        $game = Matches::find($gameO->id);
        if($game->status === 'f') return true;
        $homeTeam = Team::find($game->home);
        $awayTeam = Team::find($game->away);

        $homeScore = $game->home_score;
        $awayScore = $game->away_score;

        $data['match'] = [
            'id' => $game->id
        ];

        $maxAttack = ceil(self::ONE_MINUTE / $_ENV['MIN_ATTACK_TIME']);
        $minAttackTime = $fixMinAttackTime = $_ENV['MIN_ATTACK_TIME'];
        $maxAttackTime = $_ENV['MAX_ATTACK_TIME'];
        $totalAttackTime = 0;
        for ($i = 1; $i <= $maxAttack; $i++) {
            if ($totalAttackTime < self::ONE_MINUTE) {
                $attacker = $this->randomSelector([$homeTeam, $awayTeam]);
                $attackTime = rand($minAttackTime, $maxAttackTime);
                $totalAttackTime += $attackTime;
                $remainingMinutes = (self::ONE_MINUTE - $totalAttackTime);
                if ($remainingMinutes < $maxAttackTime) {
                    $newMinAttackTime = floor($remainingMinutes / 2);
                    if ($fixMinAttackTime < $newMinAttackTime) {
                        $minAttackTime = $newMinAttackTime;
                    }
                    if ($remainingMinutes <= $minAttackTime * 2) {
                        $minAttackTime = $remainingMinutes;
                    }
                    $maxAttackTime = $remainingMinutes;
                }

                $attackingPlayer = Player::where('team', '=', $attacker->id)->inRandomOrder()->firstOrFail();
                $assistPlayer = Player::where('team', '=', $attacker->id)->where('id', '!=', $attackingPlayer->id)->inRandomOrder()->firstOrFail();

                $attackScore = $this->randomSelector([0, 0, 0, 2, 2, 2, 3, 3]);

                $homeScore += $homeTeam->id == $attacker->id ? $attackScore : 0;
                $awayScore += $awayTeam->id == $attacker->id ? $attackScore : 0;

                $insertData = [
                    'match' => $game->id,
                    'attacker' => $attacker->id,
                    'attacking_player' => $attackingPlayer->id,
                    'assist_player' => $assistPlayer->id,
                    'attack_time' => $attackTime,
                    'score' => $attackScore,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                $data['position'][] = $insertData;

                $data['match']['home_score'] = $homeScore;
                $data['match']['away_score'] = $awayScore;


            } else {
                continue;
            }
        }
        $data['match']['minutes'] = $game->minutes + 1;
        $data['match']['status'] = $data['match']['minutes'] >= $_ENV['MAX_GAME_MINUTES'] ? 'f' : 's';

        try {
            $insertHistory = MatchesHistory::insert($data['position']);
            $matchUpdate = Matches::find($game->id)->update($data['match']);
        } catch (\Exception $exception) {
            dump($exception);
            exit();
        }

        return $data;
    }


    private function randomSelector($array)
    {
        $key = array_rand($array);
        return $array[$key];
    }
}
