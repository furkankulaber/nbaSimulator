<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Team;
use App\Services\NbaAPIServices;
use Illuminate\Console\Command;

class NbaDataMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:nbaData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nba Data Sync';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $nbaAPI = new NbaAPIServices();
        $this->migrateTeams($nbaAPI);
        $this->migratePlayer($nbaAPI);

    }

    private function migrateTeams(NbaAPIServices $nbaAPIServices)
    {
        $currentPage = 1;
        reResponse:
        $apiResponse = $nbaAPIServices->setUrl('teams')->setPage($currentPage)->getRequest()->getResponse();
        if (isset($apiResponse) && isset($apiResponse['meta']) && $apiResponse['meta']['total_count'] > 0)
        {
            Player::truncate();
            Team::truncate();
            foreach ($apiResponse['data'] as $team) {
                $insertTeam = Team::create(
                    [
                        'abbreviation' => $team['abbreviation'],
                        'conference' => $team['conference'],
                        'full_name' => $team['full_name']
                    ]
                );
            }
            if($currentPage < $apiResponse['meta']['total_pages']){
                $currentPage = $apiResponse['meta']['next_page'];
                goto reResponse;
            }
        }
    }

    private function migratePlayer(NbaAPIServices $nbaAPIServices)
    {
        $currentPage = 1;
        reResponse:
        $apiResponse = $nbaAPIServices->setUrl('players')->setPage($currentPage)->getRequest()->getResponse();
        if (isset($apiResponse) && isset($apiResponse['meta']) && $apiResponse['meta']['total_count'] > 0)
        {
            foreach ($apiResponse['data'] as $player) {
                if (isset($player['team'])) {
                    $abb = $player['team']['abbreviation'];
                    if ($abb) {
                        $pTeam = Team::where('abbreviation', $abb)->first();
                        if ($pTeam) {
                            $playerCount = Player::where('team', '=', $pTeam->id)->count();
                            if($playerCount <= 10) {
                                $insertPlayer = Player::create(
                                    [
                                        'first_name' => $player['first_name'],
                                        'last_name' => $player['last_name'],
                                        'team' => $pTeam->id
                                    ]
                                );
                            }
                        }
                    }
                }

            }
            if($currentPage < $apiResponse['meta']['total_pages']){
                $currentPage = $apiResponse['meta']['next_page'];
                goto reResponse;
            }
        }
    }
}
