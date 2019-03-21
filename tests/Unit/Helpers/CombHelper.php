<?php

namespace Tests\Unit\Helpers;

class CombHelper{
	static function giveStraight()
	{
		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		$cards = array();
		$choice_array = [1,2,3,4,5,6,7,8];
		$point_rank = $choice_array[array_rand($choice_array)];
		$slice = array_slice($numbers_array, $point_rank, 5);
		foreach ($slice as $slice_rank) {
			$suit = $suit_array[array_rand($suit_array)];
			array_push($cards, $suit.'-'.(string)$slice_rank);
		}
		$sixth_card_rank = $numbers_array[array_rand($numbers_array)];
		$sixth_card_suit = $suit_array[array_rand($suit_array)];
		while (in_array($sixth_card_suit.'-'.(string)$sixth_card_rank, $cards)) {
			$sixth_card_rank = $numbers_array[array_rand($numbers_array)];
			$sixth_card_suit = $suit_array[array_rand($suit_array)];
		}
		array_push($cards, $sixth_card_suit.'-'.(string)$sixth_card_rank);
		$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
		$seventh_card_suit = $suit_array[array_rand($suit_array)];
		while (in_array($seventh_card_suit.'-'.(string)$seventh_card_rank, $cards)) {
			$seventh_card_rank = $numbers_array[array_rand($numbers_array)];
			$seventh_card_suit = $suit_array[array_rand($suit_array)];
		}
		array_push($cards, $seventh_card_suit.'-'.(string)$seventh_card_rank);	
		return $cards;
	}
	static function giveThree(){
		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		$cards = array();
		$used_suits = array();
		$three_rank_index = array_rand($numbers_array);
		$three_rank = $numbers_array[$three_rank_index];
		unset($numbers_array[$three_rank_index]);
		$three_suit_indexes = array_rand($suit_array, 3);
		foreach ($three_suit_indexes as $index) {
				array_push($cards, $suit_array[$index].'-'.(string)$three_rank);
				array_push($used_suits, $index);
			}	
		for ($i=0; $i < 4; $i++) { 
			$rank_index = array_rand($numbers_array);
			$suit_index = array_rand($suit_array);
			array_push($used_suits, $suit_index);
			$suit = $suit_array[$suit_index];
			array_push($cards, $suit.'-'.$numbers_array[$rank_index]);
			unset($numbers_array[$rank_index]);
			foreach (array_count_values($used_suits) as $key => $value) {
				if ($value>3){
					$ind = array_search($key, $used_suits);
					foreach ($used_suits as $suit) {
						if ($suit == $key){
							unset($used_suits[array_search($suit, $used_suits)]);
						}
					}
				}
			}
		}
		return $cards;
	}
	static function giveTwo(){
		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		$cards = array();
		$used_suits = array();
		$two_rank_index = array_rand($numbers_array);
		$two_rank = $numbers_array[$two_rank_index];
		unset($numbers_array[$two_rank_index]);
		$two_suit_indexes = array_rand($suit_array, 2);
		foreach ($two_suit_indexes as $index) {
				array_push($cards, $suit_array[$index].'-'.(string)$two_rank);
				array_push($used_suits, $index);
			}
		$two_rank_index = array_rand($numbers_array);
		$two_rank = $numbers_array[$two_rank_index];
		unset($numbers_array[$two_rank_index]);
		$two_suit_indexes = array_rand($suit_array, 2);
		foreach ($two_suit_indexes as $index) {
				array_push($cards, $suit_array[$index].'-'.(string)$two_rank);
				array_push($used_suits, $index);
			}			
		for ($i=0; $i < 3; $i++) { 
			$rank_index = array_rand($numbers_array);
			$suit_index = array_rand($suit_array);
			array_push($used_suits, $suit_index);
			$suit = $suit_array[$suit_index];
			array_push($cards, $suit.'-'.$numbers_array[$rank_index]);
			unset($numbers_array[$rank_index]);
			foreach (array_count_values($used_suits) as $key => $value) {
				if ($value>3){
					$ind = array_search($key, $used_suits);
					foreach ($used_suits as $suit) {
						if ($suit == $key){
							unset($used_suits[array_search($suit, $used_suits)]);
						}
					}
				}
			}
		}
		return $cards;
	}
	static function giveOne(){
		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		$cards = array();
		$used_suits = array();
		$two_rank_index = array_rand($numbers_array);
		$two_rank = $numbers_array[$two_rank_index];
		unset($numbers_array[$two_rank_index]);
		$two_suit_indexes = array_rand($suit_array, 2);
		foreach ($two_suit_indexes as $index) {
				array_push($cards, $suit_array[$index].'-'.(string)$two_rank);
				array_push($used_suits, $index);
			}			
		for ($i=0; $i < 5; $i++) { 
			$rank_index = array_rand($numbers_array);
			$suit_index = array_rand($suit_array);
			array_push($used_suits, $suit_index);
			$suit = $suit_array[$suit_index];
			array_push($cards, $suit.'-'.$numbers_array[$rank_index]);
			unset($numbers_array[$rank_index]);
			foreach (array_count_values($used_suits) as $key => $value) {
				if ($value>3){
					$ind = array_search($key, $used_suits);
					foreach ($used_suits as $suit) {
						if ($suit == $key){
							unset($used_suits[array_search($suit, $used_suits)]);
						}
					}
				}
			}
		}
		return $cards;
	}
	static function giveKicker(){
		$suit_array = ['spades', 'diamonds', 'hearts', 'clubs'];  
		$numbers_array = [1,2,3,4,5,6,7,8,9,10,11,12,13];
		$cards = array();
		$used_suits = array();
		for ($i=0; $i < 7; $i++) { 
			$rank_index = array_rand($numbers_array);
			$suit_index = array_rand($suit_array);
			array_push($used_suits, $suit_index);
			$suit = $suit_array[$suit_index];
			array_push($cards, $suit.'-'.$numbers_array[$rank_index]);
			unset($numbers_array[$rank_index]);
			foreach (array_count_values($used_suits) as $key => $value) {
				if ($value>3){
					$ind = array_search($key, $used_suits);
					foreach ($used_suits as $suit) {
						if ($suit == $key){
							unset($used_suits[array_search($suit, $used_suits)]);
						}
					}
				}
			}
		}
		return $cards;
	}
}
?>
