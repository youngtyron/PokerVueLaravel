<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Hand;
use App\Player;
use App\Game;
use App\Round;

class HandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRoyalFlushOnHand()
    {
    	$game = Game::create();
    	$round = Round::create(['game_id'=>$game->id]);
    	$user = factory(\App\User::class)->create();
    	$player = Player::create(['game_id'=>$game->id, 'user_id'=>$user->id]);
    	$hand = Hand::create(['player_id'=>$player->id]);
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];
    	$royal_suit = array_rand($suit_array);
    	$cards = [$royal_suit.'-1', $royal_suit.'-13', $royal_suit.'-12', $royal_suit.'-11', $royal_suit.'-10'];
    	$sixth_card_suit = array_rand($suit_array);
    	if ($sixth_card_suit != $royal_suit){
    		$sixth_card_rank = rand(1, 13);
    	}
    	else{
    		$sixth_card_rank = rand(2, 9);
    	}
    	array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
    	$seventh_card_suit = array_rand($suit_array);
    	if ($seventh_card_suit != $royal_suit and $seventh_card_suit != $sixth_card_suit){
    		$seventh_card_rank = rand(1, 13);
    	}
    	else if ($seventh_card_suit != $sixth_card_suit){
    		$seventh_card_rank = rand(2, 9);
    	}
    	else if ($seventh_card_suit != $royal_suit){
    		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
    		$index = array_search($sixth_card_rank, $numbers_array);
    		array_splice($numbers_array, $index, 1);
    		$seventh_card_rank = array_rand($numbers_array);
    	}
    	else{
    		$numbers_array = [1,2,3,4,5,6,7,8,9];
    		$index = array_search($sixth_card_rank, $numbers_array);
    		array_splice($numbers_array, $index, 1);
    		$seventh_card_rank = array_rand($numbers_array);    		
    	}
		array_push($cards, $seventh_card_suit.'-'.(string)$seventh_card_rank);

   		dd($cards);
    	
    	$hand->first_card = $cards[0];
    	$hand->second_card = $cards[1];
    	$hand->save();
    	$round->first_card = $cards[2];
		$round->second_card = $cards[3];
		$round->third_card = $cards[4];
		$round->fourth_card = $cards[5];
		$round->fifth_card = $cards[6];
		$round->save();
		$this->assertEquals($hand->combination(), 10);
    }
}
