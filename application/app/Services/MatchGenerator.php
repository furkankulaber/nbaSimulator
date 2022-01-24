<?php

namespace App\Services;

use App\Models\Matches;
use App\Models\MatchesHistory;
use App\Models\Team;

class MatchGenerator
{

    public function generataMatch()
    {
        Matches::truncate();
        MatchesHistory::truncate();
        $allTeamGroupWithConference = Team::all();

        try {
            foreach ($allTeamGroupWithConference->toArray() as $team) {
                $teamsArray[] = $team['id'];
            }
            $response = $this->fixtureGenerate($allTeamGroupWithConference->toArray());
            $this->matchInsert($response);
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    private function fixtureGenerate($teams): array
    {
        $teamsArray = sizeof($teams);

        $totalRounds = $teamsArray - 1;
        $matchesPerRound = $teamsArray / 2;
        $rounds = array();
        for ($i = 0; $i < $totalRounds; $i++) {
            $rounds[$i] = array();
        }

        for ($round = 0; $round < $totalRounds; $round++) {
            for ($match = 0; $match < $matchesPerRound; $match++) {
                $home = ($round + $match) % ($teamsArray - 1);
                $away = ($teamsArray - 1 - $match + $round) % ($teamsArray - 1);
                if ($match == 0) {
                    $away = $teamsArray - 1;
                }
                $rounds[$round][$match] = [
                    'home' => $teams[$home],
                    'away' => $teams[$away]
                ];
            }
        }

        $interleaved = array();
        for ($i = 0; $i < $totalRounds; $i++) {
            $interleaved[$i] = array();
        }

        $evn = 0;
        $odd = ($teamsArray / 2);
        for ($i = 0; $i < sizeof($rounds); $i++) {
            if ($i % 2 == 0) {
                $interleaved[$i] = $rounds[$evn++];
            } else {
                $interleaved[$i] = $rounds[$odd++];
            }
        }

        $rounds = $interleaved;

        for ($round = 0; $round < sizeof($rounds); $round++) {
            if ($round % 2 == 1) {
                $rounds[$round][0] = $this->flip($rounds[$round][0]);
            }
        }

        $round_counter = count($rounds) - 1;
        for ($i = $round_counter; $i >= 0; $i--) {
            foreach ($rounds[$i] as $r) {
                $rounds[$round_counter + (($round_counter + 1) - $i)][] = $this->flip($r);
            }
        }

        return $rounds;
    }

    private function flip($match)
    {
        $tmp = $match['away'];
        $match['away'] = $match['home'];
        $match['home'] = $tmp;
        return $match;
    }

    private function matchInsert($fixtures)
    {
        foreach ($fixtures as $week => $fixture) {
            foreach ($fixture as $match) {
                Matches::create([
                    'home' => $match['home']['id'],
                    'away' => $match['away']['id'],
                    'week' => $week + 1
                ]);
            }
        }
    }

}
