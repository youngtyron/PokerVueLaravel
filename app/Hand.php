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
  public function allSeven(){
  	$round = $this->player->game->round;
  	$cards = array($this->first_card, $this->second_card, $round->first_card, $round->second_card, $round->third_card, $round->fourth_card, $round->fifth_card);
  	return $cards;
  }
  public function ranks(){
  	$cards = $this->allSeven();
  	$ranks = array();
  	foreach ($cards as $card){
  		if ($card!=null){
  			$rank = explode('-', $card)[1];
			array_push($ranks, $rank);
  		}
  	}
  	return $ranks;
  }
  public function equally_ranks_combination(){
  	$ranks = $this->ranks();
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
}
