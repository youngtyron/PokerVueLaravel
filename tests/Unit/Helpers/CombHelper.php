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
}
?>
