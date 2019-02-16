<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hand extends Model
{
  protected $guarded = [];
  public $timestamps = false;

  public function player(){
    return $this->belongsTo('App\Player');
  }
  public function allcards(){
  	$round = $this->player->game->round;
  	$cards = array($this->first_card, $this->second_card, $round->first_card, $round->second_card, $round->third_card, $round->fourth_card, $round->fifth_card);
  	return $cards;
  }
  public function ranks(){
  	$cards = $this->allcards();
  	$ranks = array();
  	foreach ($cards as $card){
  		if ($card!=null){
  			$rank = explode('-', $card)[1];
			array_push($ranks, $rank);
  		}
  	}
  	return $ranks;
  }
  public function suits(){
  	$cards = $this->allcards();
  	$suits = array();
  	foreach ($cards as $card){
  		if ($card!=null){
  			$suit = explode('-', $card)[0];
			array_push($suits, $suit);
  		}
  	}
  	return $suits;
  }
  public function combination(){
  	$cards = $this->allcards_array();
  	if ($this->royal_and_straight_flush($cards)=='R'){
  		$combination = 'royal flush';
  	}
  	else if ($this->royal_and_straight_flush($cards)=='S'){
  		$combination = 'straight flush';
  	}
  	else {
  		if ($this->flush($this->suits())){
  			if ($this->equal_ranks_combination($this->ranks())!='full house'){
  				$combination = 'flush';
  			}
  			else {
  				$combination = 'full house';
  			}
  		}
  		else {
  			if ($this->straight($this->ranks())){
  				$combination = 'straight';
  			}
  			else {
  				$combination = $this->equal_ranks_combination($this->ranks());
  			}
  		}
  	}
  	return $combination;
  }
  public function allcards_array(){
  	$cards = $this->allcards();
  	$array = array();
  	foreach ($cards as $index => $card){
		if ($card!=null){
			$suit = explode('-', $card)[0];
			$rank = explode('-', $card)[1];
			array_push($array, array('suit' => $suit, 'rank'=>$rank));
		}
  	}
	return $array;
  }
  public function royal_and_straight_flush($cards){
	$card_row= array();
	$ranks_row = array();
	foreach($cards as $card){
		if (($card['rank'] == 1 and  !(in_array($card['rank'], $ranks_row))) or
			($card['rank'] == 13 and !(in_array($card['rank'], $ranks_row))) or
			($card['rank'] == 12 and !(in_array($card['rank'], $ranks_row))) or
			($card['rank'] == 11 and !(in_array($card['rank'], $ranks_row))) or
			($card['rank'] == 10 and !(in_array($card['rank'], $ranks_row))) 
		){
			array_push($card_row, $card);
			array_push($ranks_row, $card['rank']);
		}
	}
	if (count($card_row)==5){
		$suit = $card_row[0]['suit'];
		$counter = 0;
		foreach ($card_row as $card) {
			if ($card['suit']==$suit){
				$counter +=1;
				continue;
			}
		}
		if ($counter==5){
			return 'R';
		}
		else {
			return 'S';
		}
	}		
	else{
		return false;
	}
  }
  public function equal_ranks_combination($ranks){
  	$freq = array_count_values ($ranks);
  	$pairs = 0;
  	$triples = 0;
  	$quads = 0;
  	foreach ($freq as $key => $value) {
		if ($value == 2){
			$pairs +=1;
		}
		else if ($value == 3){
			$triples +=1;
		}
		else if ($value == 4){
			$quads +=1;
		}
  	}
  	if ($quads !=0){
  		return 'quads';
  	}
  	else if ($pairs == 1 and $triples == 0){
  		return 'one pair';
  	}
  	else if ($pairs == 2 and $triples == 0){
  		return 'two pair';
  	}
  	else if ($pairs == 1 and $triples == 1){
  		return 'full house';
  	}
  	else if ($pairs == 0 and $triples == 1){
  		return 'three';
  	}
  	else {
  		return 'kicker';
  	}
  }
  public function straight($ranks){
  	sort($ranks);
  	$min = min($ranks);
  	$straight = false;
  	for ($i=0; $i < 3; $i++) { 
  		if (in_array($min+1, $ranks) and 
	  		in_array($min+2, $ranks) and
	  		in_array($min+3, $ranks) and
	  		in_array($min+4, $ranks)
  		){
  			$straight = true;
  			break;
  		}
  		else{
  			unset($ranks[0]);
  			$min = min($ranks);
  		}
  	}
  	return $straight;
  }
  public function flush($suits){
  	$freq = array_count_values ($suits);
  	$flush = false;
  	foreach ($freq as $key => $value){
  		if ($value == 5){
  			$flush = true;
  			break;
  		}
  	}
 	return $flush;
  }
}
