<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Player;
use App\Game;
use App\Round;

class CirculationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFivePlayersArrangementBlindBets()
    {
        $users = factory(\App\User::class, 5)->create();
        $game = Game::create();
        foreach ($users as $user) {
            $player = Player::create(['user_id'=>$user->id, 'game_id'=>$game->id]);
        }
        $round = Round::create(['game_id'=>$game->id]);
        $round->settleQueue();
        $players = $game->players;
        $small_blind = $players->find($round->small_blind_id);
        $this->assertEquals($small_blind->turn, 1);
        $big_blind = $players->find($round->big_blind_id);
        $this->assertEquals($big_blind->turn, 2);
        $current = $players->find($round->current_player_id);
        $this->assertEquals($current->turn, 3);
        $button = $players->find($round->button_id);
        $this->assertEquals($button->turn, 5);
        $verifying_array = array();
        $real_turn_array = array();
 		for ($i=1; $i < count($players); $i++) { 
 			array_push($verifying_array, $i);
 			array_push($real_turn_array, $players[$i-1]->turn);
 		}
 		$this->assertEquals(sort($verifying_array), sort($real_turn_array));

    }
}
