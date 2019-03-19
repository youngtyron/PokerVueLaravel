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
  public function highestCard($N){
    $cards = $this->allcards_array();
    $ranks = $this->ranks($cards);
    sort($ranks);
    $end_point = count($ranks) - $N;
    $ranks = array_slice($ranks, 0, $end_point);
    $highestCard = max($ranks);
    return $highestCard;
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
  public function ranks($cards){
  	$ranks = array();
  	foreach ($cards as $card){
		  array_push($ranks , $card['rank']);
  	}
  	return $ranks;
  }
  public function suits($cards){
  	$suits = array();
  	foreach ($cards as $card){
 		array_push($suits , $card['suit']);
  	}
  	return $suits;
  }
  public function combination(){
  	$cards = $this->allcards_array();
  	$equals = $this->equal_ranks_combination($this->ranks($cards));
  	if ($this->straight($this->ranks($cards))){
  		if ($this->royal_and_straight_flush($cards)=='R'){
  			$combination = 10;
  		}
  		else if ($this->royal_and_straight_flush($cards)=='S'){
  			$combination = 9;
	  	}
	  	else {
			if ($this->flush($this->suits($cards))){
	  			$combination = 6;
	  		}
	  		else {
	  			$combination = 5;
	  		}
	  	}
  	}
  	else {
  		if ($this->flush($this->suits($cards))){
			$combination = 6;
	  	}
	  	else {
	  		$combination = $equals;
	  	}
  	}
  	return $combination;
  }
  public function name_of_combination($rate){
    if ($rate == 1){
      $combination = 'High card';
    }
    else if ($rate == 2){
      $combination = 'One pair';
    }
    else if ($rate == 3){
      $combination = 'Two pair';
    }
    else if ($rate == 4){
      $combination = 'Three of a Kind';
    }
    else if ($rate == 5){
      $combination = 'Straight';
    }
    else if ($rate == 6){
      $combination = 'Flush';
    }
    else if ($rate == 7){
      $combination = 'Full House';
    }
    else if ($rate == 8){
      $combination = 'Four of a Kind';
    }
    else if ($rate == 9){
      $combination = 'Straight Flush';
    }
    else if ($rate == 10){
      $combination = 'Royal Flush';
    }
    return $combination;
  }
  public function royal_and_straight_flush($cards){
  	$suits = $this->suits($cards);
  	$freq = array_count_values ($suits);
  	$combo_siut = false;
  	foreach ($freq as $key => $value){
  		if ($value >= 5){
  			$combo_siut = $key;
  			break;
  		}
  	}
  	if ($combo_siut == false) {
  		return false;
  	}
  	else {
  		$rank_row = array();
  		foreach ($cards as $card) {
  			if ($card['suit']==$combo_siut){
  				array_push($rank_row, $card['rank']);
  			}
  		}
		sort($rank_row);
      $intersect = array_intersect($rank_row, [1, 10, 11, 12, 13]);
      if (in_array(1, $intersect) and in_array(10, $intersect) and in_array(11, $intersect) and in_array(12, $intersect) 
                                  and in_array(13, $intersect)){
  			return 'R';
  		}
  		else {
        $counter = 0;
        foreach ($rank_row as $rank) {
          if(in_array($rank+1, $rank_row)){
            $counter += 1;
          }
        }
        if ($counter>=4){
          return 'S';
        }
        else {
         return false;
        }
  		}
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
  		return 8;
  	}
  	else if ($pairs == 1 and $triples == 0){
  		return 2;
  	}
  	else if ($pairs == 2 and $triples == 0){
  		return 3;
  	}
  	else if ($pairs == 1 and $triples == 1){
  		return 7;
  	}
    else if ($triples == 2 and $pairs == 0){
      return 7;
    }
    else if ($triples == 1 and $pairs == 2){
      return 7;
    }
  	else if ($pairs == 0 and $triples == 1){
  		return 4;
  	}
  	else {
  		return 1;
  	}
  }
  public function straight($ranks){
  	if (in_array(1, $ranks)){
  		array_push($ranks, 14);
  	}
  	sort($ranks);
  	$min = min($ranks);
  	$straight = false;
  	for ($i=0; $i < 4; $i++) { 
  		if (in_array($min+1, $ranks) and 
	  		in_array($min+2, $ranks) and
	  		in_array($min+3, $ranks) and
	  		in_array($min+4, $ranks)
  		){
  			$straight = true;
  			break;
  		}
  		else{
  			array_splice($ranks, 0, 1);
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
