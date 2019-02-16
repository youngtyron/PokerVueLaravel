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
  public function players(){
    $players = Player::where('game_id', $this->game_id)->where('passing', 0)->get();
    return $players;
    // return $this->game->players;
  }
  public function winner(){
    if (count($this->players())==1){
      return $this->players()[0];
    }
    else {
      if ($this->phase == 'showdown'){
        //Проверка комбинаций
      }
      else{
        return null;
      }
    }
  }
  public function zeroRound(){
     $this->current_player_id = $this->small_blind_id;
     $this->betted = 0;
     $this->max_bet = 0;
     $this->save();
     foreach ($this->players() as $p){
       $p->last_bet = null;
       $p->save();
     }
     return true;
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
  public function whoMustCallNext(){
    $next = null;
    $players = $this->players();
    $extendedPlayers = $this->game->players;
    $current = Player::find($this->current_player_id);
    foreach ($extendedPlayers as $p) {
      if ($p->id == $current->id){
        $current_index = array_search($p, $extendedPlayers->all());
      }
    }
    for($i = 1; $i<=count($players); ++$i){
      if ($current_index+$i >= count($players)){
        $cycle_index = $i - (count($players) - $current_index);
        if ($players[$cycle_index]->last_bet < $this->max_bet){
          $next = $players[$cycle_index];
          break;
        }
      }
      else{
        $cycle_index = $current_index+$i;
      }
    }
    if ($next){
      return $next;
    }
    else {
      return null;
    }
  }
  public function nextStep(){
    if ($this->phase == 'blind-bets'){
      $this->dealPreflop();
    }
    else if ($this->phase == 'preflop'){
      $this->dealFlop();
    }
    else if ($this->phase == 'flop'){
      $this->dealTurn();
    }
    else if ($this->phase == 'turn'){
      $this->dealRiver();
    }
    else if ($this->phase == 'river'){
      $this->phase = 'shotdown';
      $this->save();
    }
    return true;
  }
  public function dealPreflop(){
    $players = $this->game->players;
    //Вероятно этот кусок не нужен: начало->
    $cardsonhands = array();
    foreach ($players as $player){
      if ($player->hand){
        array_push($cardsonhands, $player->hand->first_card);
        array_push($cardsonhands, $player->hand->second_card);
      };
    };
    //<-конец
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
    $this->phase = 'preflop';
    $this->zeroRound();
    $this->save();
    return true;
  }
  public function dealFlop(){
    $players = $this->game->players;
    $cardsonhands = array();
    foreach ($players as $player){
      if ($player->hand){
        array_push($cardsonhands, $player->hand->first_card);
        array_push($cardsonhands, $player->hand->second_card);
      };
    };
    $flop = array_diff($this->generateDeck(), $cardsonhands);
    $first = $flop[array_rand($flop, 1)];
    unset($flop[array_search($first, $flop)]);
    $second = $flop[array_rand($flop, 1)];
    unset($flop[array_search($second, $flop)]);
    $third = $flop[array_rand($flop, 1)];
    unset($flop[array_search($third, $flop)]);
    $this->first_card = $first;
    $this->second_card = $second;
    $this->third_card = $third;
    $this->phase = 'flop';
    $this->zeroRound();
    $this->save();
    return true;
  }
  public function dealTurn(){
    $players = $this->game->players;
    $cardsonhands = array();
    foreach ($players as $player){
      if ($player->hand){
        array_push($cardsonhands, $player->hand->first_card);
        array_push($cardsonhands, $player->hand->second_card);
      };
    };
    array_push($cardsonhands, $this->first_card);
    array_push($cardsonhands, $this->second_card);
    array_push($cardsonhands, $this->third_card);
    $turn = array_diff($this->generateDeck(), $cardsonhands);
    $fourth = $turn[array_rand($turn, 1)];
    $this->fourth_card = $fourth;
    $this->phase = 'turn';
    $this->zeroRound();
    $this->save();
    return true;
  }
  public function dealRiver(){
    $players = $this->game->players;
    $cardsonhands = array();
    foreach ($players as $player){
      if ($player->hand){
        array_push($cardsonhands, $player->hand->first_card);
        array_push($cardsonhands, $player->hand->second_card);
      };
    };
    array_push($cardsonhands, $this->first_card);
    array_push($cardsonhands, $this->second_card);
    array_push($cardsonhands, $this->third_card);
    array_push($cardsonhands, $this->fourth_card);
    $river = array_diff($this->generateDeck(), $cardsonhands);
    $fifth = $river[array_rand($river, 1)];
    $this->fifth_card = $fifth;
    $this->phase = 'river';
    $this->zeroRound();
    $this->save();
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
      if ($big_blind_index+1 == count($players)){
        $current_player_index = 0;
      }
      else{
        $current_player_index = $big_blind_index + 1;
      }
      $this->current_player_id = $players[$current_player_index]->id;
      $this->save();
    }
    return true;
  }
}
