<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
  protected $guarded = [];
  public $timestamps = false;

  public function game(){
    return $this->belongsTo('App\Game');
  }
  public function generateDeck(){
    $deck = array();
    for ($r = 1; $r<14; $r++){
      array_push($deck, 'spades-'.$r);
      array_push($deck, 'hearts-'.$r);
      array_push($deck, 'diamonds-'.$r);
      array_push($deck, 'clubs-'.$r);
    }
    if ($this->phase == 'preflop'){
      //
    }
    else if ($this->phase == 'flop'){
      //
    }
    else if ($this->phase == 'turn'){
      //
    }
    else if ($this->phase == 'river'){
      //
    }
    return $deck;
  }
  public function dealPreflop(){
    $players = $this->game->players;
    $cardsonhands = array();
    foreach ($players as $player){
      if ($player->hand){
        array_push($cardsonhands, $player->hand->first_card);
        array_push($cardsonhands, $player->hand->second_card);
      };
    };
    $preflop = array_diff($this->generateDeck(), $cardsonhands);
    foreach ($players as $player){
      if (!$player->hand or !$player->hand->first_card){
        $hand = Hand::firstOrCreate(['player_id'=>$player->id]);
        $f = $preflop[array_rand($preflop, 1)];
        unset($preflop[array_search($f, $preflop)]);
        $s = $preflop[array_rand($preflop, 1)];
        unset($preflop[array_search($s, $preflop)]);
        $hand->first_card = $f;
        $hand->second_card = $s;
        $hand->save();
      };
    };
    return true;
  }
  public function playersArrangement(){
    if ($this->phase == 'blind-bets' and !$this->button_id){
      $players = $this->game->players;
      $button = $players[array_rand($players->toArray(), 1)];
      $button_index = array_search($button, $players->all());
      if ($button_index+1 == count($players)){
        $small_blind_index = 0;
      }
      else{
        $small_blind_index = $button_index + 1;
      }
      if ($small_blind_index+1 == count($players)){
        $big_blind_index = 0;
      }
      else{
        $big_blind_index = $small_blind_index + 1;
      }
      $small_blind = $players[$small_blind_index];
      $big_blind = $players[$big_blind_index];
      $this->small_blind_id = $small_blind->id;
      $this->big_blind_id = $big_blind->id;
      $this->button_id = $button->id;
      $this->current_player_id = $small_blind->id;
      $this->save();
    }
    return true;
  }
}
