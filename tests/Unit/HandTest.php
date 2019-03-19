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
    public function testRoyalFlushOnHand(){
    	$game = Game::create();
    	$round = Round::create(['game_id'=>$game->id]);
    	$user = factory(\App\User::class)->create();
    	$player = Player::create(['game_id'=>$game->id, 'user_id'=>$user->id]);
    	$hand = Hand::create(['player_id'=>$player->id]);
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];
    	$royal_suit = $suit_array[array_rand($suit_array)];
    	$cards = [$royal_suit.'-1', $royal_suit.'-13', $royal_suit.'-12', $royal_suit.'-11', $royal_suit.'-10'];
    	$sixth_card_suit = $suit_array[array_rand($suit_array)];
    	if ($sixth_card_suit != $royal_suit){
    		$sixth_card_rank = rand(1, 13);
    	}
    	else{
    		$sixth_card_rank = rand(2, 9);
    	}
    	array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
    	$seventh_card_suit = $suit_array[array_rand($suit_array)];
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
    public function testStrightFlushOnHand(){
    	$game = Game::create();
    	$round = Round::create(['game_id'=>$game->id]);
    	$user = factory(\App\User::class)->create();
    	$player = Player::create(['game_id'=>$game->id, 'user_id'=>$user->id]);
    	$hand = Hand::create(['player_id'=>$player->id]);
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];
    	$main_suit = $suit_array[array_rand($suit_array)];
    	$combostart = rand(1, 9);
    	$cards = [$main_suit.'-'.(string)$combostart, $main_suit.'-'.(string)($combostart+1), 
    			  $main_suit.'-'.(string)($combostart+2), $main_suit.'-'.(string)($combostart+3), 
    			  $main_suit.'-'.(string)($combostart+4)];
    	$sixth_card_suit = $suit_array[array_rand($suit_array)];
    	if ($sixth_card_suit == $main_suit and $combostart == 9){
    		$numbers_array = [2,3,4,5,6,7,8];
    		$sixth_card_rank = $numbers_array[array_rand($numbers_array)];
    	}
    	else if ($sixth_card_suit == $main_suit){
    		$comboarr = array();
    		for ($i=0; $i < 5; $i++) { 
    			array_push($comboarr, $combostart + $i);
	   		}
	   		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
	   		$available = array_diff($numbers_array, $comboarr);
	   		$sixth_card_rank = $available[array_rand($available)];
    	}
    	else{
    		$sixth_card_rank = rand(1, 13);
    	}
    	array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
    	$seventh_card_suit = $suit_array[array_rand($suit_array)];
    	if ($seventh_card_suit == $main_suit and $seventh_card_suit == $sixth_card_suit){
    		if ($combostart == 9){
    			$numbers_array = [2,3,4,5,6,7,8];
    			$six_index = array_search($sixth_card_rank, $numbers_array);
    			unset($numbers_array[$six_index]);
    			$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
    		}
    		else{
	    		$comboarr = array();
	    		for ($i=0; $i < 5; $i++) { 
	    			array_push($comboarr, $combostart + $i);
		   		}
		   		array_push($comboarr, $sixth_card_rank);
		   		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		   		$available = array_diff($numbers_array, $comboarr);
		   		$seventh_card_rank = $available[array_rand($available)];
    		}
    	}
    	else if ($seventh_card_suit == $main_suit){
    		$comboarr = array();
    		for ($i=0; $i < 5; $i++) { 
    			array_push($comboarr, $combostart + $i);
	   		}
	   		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
	   		$available = array_diff($numbers_array, $comboarr);
	   		$seventh_card_rank = $available[array_rand($available)];
    	}
    	else if ($seventh_card_suit == $sixth_card_suit){
    		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
    		$six_index = array_search($sixth_card_rank, $numbers_array);
    		unset($numbers_array[$six_index]);
    		$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
    	}
    	else {
    		$seventh_card_rank = rand(1, 13);
    	}
 		array_push($cards, $seventh_card_suit.'-'.(string)$seventh_card_rank);
    	$hand->first_card = $cards[0];
    	$hand->second_card = $cards[1];
    	$hand->save();
    	$round->first_card = $cards[2];
		$round->second_card = $cards[3];
		$round->third_card = $cards[4];
		$round->fourth_card = $cards[5];
		$round->fifth_card = $cards[6];
		$round->save();
		$this->assertEquals($hand->combination(), 9);
    }
    public function testFourOnHand(){
    	$game = Game::create();
    	$round = Round::create(['game_id'=>$game->id]);
    	$user = factory(\App\User::class)->create();
    	$player = Player::create(['game_id'=>$game->id, 'user_id'=>$user->id]);
    	$hand = Hand::create(['player_id'=>$player->id]);
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
    	$rank_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
    	$four_rank = $rank_array[array_rand($rank_array)];
    	$cards = ['spades-'.(string)$four_rank, 'diamonds-'.(string)$four_rank, 
    			  'hearts-'.(string)$four_rank,'clubs-'.(string)$four_rank];
    	$fifth_card_suit = $suit_array[array_rand($suit_array)];
    	$index = array_search($four_rank, $rank_array);
    	unset($rank_array[$index]);
    	$fifth_card_rank = $rank_array[array_rand($rank_array)];
		array_push($cards, $fifth_card_suit.'-'.(string)$fifth_card_rank);
		$sixth_card_suit = $suit_array[array_rand($suit_array)];
		if ($sixth_card_suit == $fifth_card_suit){
			$numbers_array = $rank_array;
			$fifth_index = array_search($fifth_card_rank, $numbers_array);
    		unset($numbers_array[$fifth_index]);
    		$sixth_card_rank = $numbers_array[array_rand($numbers_array)];
		}
		else{
			$sixth_card_rank = $rank_array[array_rand($rank_array)];
		}
		array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
    	$seventh_card_suit = $suit_array[array_rand($suit_array)];
    	if ($seventh_card_suit == $fifth_card_suit and $seventh_card_suit == $sixth_card_suit){
			$sixth_index = array_search($sixth_card_rank, $numbers_array);
    		unset($numbers_array[$sixth_index]);
    		$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
    	}
    	else if ($seventh_card_suit == $fifth_card_suit){
			$numbers_array = $rank_array;
			$fifth_index = array_search($fifth_card_rank, $numbers_array);
    		unset($numbers_array[$fifth_index]);
    		$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
    	}
    	else if ($seventh_card_suit == $sixth_card_suit){			
    		$numbers_array = $rank_array;
			$sixth_index = array_search($sixth_card_rank, $numbers_array);
    		unset($numbers_array[$sixth_index]);
    		$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
    	}
    	else{
    		$seventh_card_rank = $rank_array[array_rand($rank_array)];
    	}
    	array_push($cards, $seventh_card_suit.'-'.(string)$seventh_card_rank);	
    	$hand->first_card = $cards[0];
    	$hand->second_card = $cards[1];
    	$hand->save();
    	$round->first_card = $cards[2];
		$round->second_card = $cards[3];
		$round->third_card = $cards[4];
		$round->fourth_card = $cards[5];
		$round->fifth_card = $cards[6];
		$round->save();
		$this->assertEquals($hand->combination(), 8);     	
    }
    public function testFullHouseOnHand(){
    	$game = Game::create();
    	$round = Round::create(['game_id'=>$game->id]);
    	$user = factory(\App\User::class)->create();
    	$player = Player::create(['game_id'=>$game->id, 'user_id'=>$user->id]);
    	$hand = Hand::create(['player_id'=>$player->id]);
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
    	$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
    	$trio = $numbers_array[array_rand($numbers_array)];
    	$trio_index = array_search($trio, $numbers_array);
    	unset($numbers_array[$trio_index]);
    	$pair_index = array_rand($numbers_array);
    	$pair = $numbers_array[$pair_index];
    	$cards = array();    
    	for ($i=0; $i < 3; $i++) { 
    		$index = array_rand($suit_array);
    		$suit = $suit_array[$index];
    		unset($suit_array[$index]);
    		$cards = array_merge($cards, [$suit.'-'.(string)$trio]);
    	}
    	$suit_array = ['spades', 'diamonds', 'hearts', 'clubs']; 
    	for ($i=0; $i < 2; $i++) { 
    		$index = array_rand($suit_array);
    		$suit = $suit_array[$index];
    		unset($suit_array[$index]);
    		$cards = array_merge($cards, [$suit.'-'.(string)$pair]);
    	}
    	$sixth_card_rank_index =  array_rand($numbers_array);
    	$sixth_card_rank = $numbers_array[$sixth_card_rank_index];
    	if ($sixth_card_rank == $pair){
    		$sixth_card_suit = $suit_array[array_rand($suit_array)];
    		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs']; 
    	}
    	else {
    		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs']; 
    		$sixth_card_suit = $suit_array[array_rand($suit_array)];
    	}
    	array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
    	$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
    	unset($numbers_array[$trio_index]);
    	unset($numbers_array[$pair_index]);
    	$seventh_card_rank_index =  array_rand($numbers_array);
    	$seventh_card_rank = $numbers_array[$seventh_card_rank_index];
    	$seventh_card_suit = $suit_array[array_rand($suit_array)];
    	array_push($cards, $seventh_card_suit.'-'.(string)$seventh_card_rank);
    	$hand->first_card = $cards[0];
    	$hand->second_card = $cards[1];
    	$hand->save();
    	$round->first_card = $cards[2];
		$round->second_card = $cards[3];
		$round->third_card = $cards[4];
		$round->fourth_card = $cards[5];
		$round->fifth_card = $cards[6];
		$round->save();
		$this->assertEquals($hand->combination(), 7);   
    }

}
