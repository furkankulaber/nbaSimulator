<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MatchesHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches_history', function (Blueprint $table) {
            $table->id();
            $table->integer('match');
            $table->integer('attacker');
            $table->integer('attacking_player');
            $table->integer('assist_player')->nullable(true)->default(null);
            $table->integer('attack_time')->default(0);
            $table->integer('score')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches_history');
    }
}
