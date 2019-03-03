<?php

namespace App;
use Carbon\Carbon;
use Cache;
use App\Player;

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
  }
  public function writeCache(){
    $expiresAt = Carbon::now()->addMinutes(100);
    $data = array('bank'=>$this->bank, 
                  'results'=>$this->results(),
                  'community'=>$this->community_cards());
    Cache::put('result.'.$this->game->id, $data, $expiresAt);  
    return true;
  }
  public function registerBet($player, $bet){
    $player->money = $player->money - $bet;
    $player->last_bet = $player->last_bet+$bet;
    $player->save();
    if ($this->betted < count($this->players)){
      $this->betted += 1;
    }
    if ($player->last_bet > $this->max_bet){
      $this->max_bet = $player->last_bet;
    }
    $this->bank = $this->bank + $bet;
    $this->save();
  }
  public function results(){
    $results = array();
    $winner_id = $this->winner();
    foreach ($this->players() as $player){
      $player_info = $player->infoArray();
      if ($player->id == $winner_id){
        $player_info += ['winner'=>true];
      }
      array_push($results, $player_info);
    }
    return $results;
  }
  public function community_cards(){
    $array = array('first_card'=>'/cards/'.$this->first_card.'.png',
                  'second_card'=>'/cards/'.$this->second_card.'.png',
                  'third_card'=>'/cards/'.$this->third_card.'.png',
                  'fourth_card'=>'/cards/'.$this->fourth_card.'.png',
                  'fifth_card'=>'/cards/'.$this->fifth_card.'.png');
    return $array;
  }
  public function combinations(){
    $combinations = array();
    $rates = array();
    foreach ($this->players() as $player) {
      array_push($combinations, array('player'=>$player->user->id, 'rate'=>$player->hand->combination()));
      array_push($rates, $player->hand->combination());
    }
    $combinations += ['rates'=>$rates];
    return $combinations;
  }
  public function winner(){
    if (count($this->players())==1){
      return $this->players()[0];
    }
    else {
      if ($this->phase == 'shotdown'){
        $combinations = $this->combinations();
        $rates = $combinations['rates'];
        $max = max($rates);
        foreach ($combinations as $combination) {
          if ($combination['rate']==$max){
            $winner = $combination['player'];
            break;
          }
        }
        return $winner;
      }
      else{
        return null;
      }
    }
  }
  public function blinds(){
    // $this->playersArrangement();
    $small_blind = Player::find($this->small_blind_id);
    $small_blind->money = $small_blind->money - 5;
    $small_blind->save();
    $big_blind = Player::find($this->big_blind_id);
    $big_blind->money = $big_blind->money - 10;
    $big_blind->save();
    $this->bank = 15;
    $this->save();
    $this->dealPreflop();
    return true;
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
  public function nextMover($current_index){
    $players = $this->players();
    $next = Null;
    for($i = 1; $i<count($players)+1; ++$i){
      if ($current_index+$i >= count($players)){
        $cycle_index = 0;
      }
      else{
        $cycle_index = $current_index+$i;
      }
      if ($players[$cycle_index]->last_bet < $this->max_bet){
        $next = $players[$cycle_index];
        break;
      }
    }
    return $next;
  }
  public function whoMustCallNext(){
    $next = null;
    $players = $this->players();
    $extendedPlayers = $this->game->players;
    $current = Player::find($this->current_player_id);
    foreach ($extendedPlayers as $index => $p) {
      if ($p->id == $current->id){
        $current_index = $index;
      }
    }
    for($i = 1; $i<count($players)+1; ++$i){
      if ($current_index+$i >= count($players)){
        $cycle_index = 0;
        if ($players[$cycle_index]->last_bet < $this->max_bet){
          $next = $players[$cycle_index];
          break;
        }
      }
      else{
        $cycle_index = $current_index+$i;
        if ($players[$cycle_index]->last_bet < $this->max_bet){
          $next = $players[$cycle_index];
          break;
        }
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
    // if ($this->phase == 'blind-bets'){
    //   $this->dealPreflop();
    // }
    if ($this->phase == 'preflop'){
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
    }
    $this->current_player_id = $this->small_blind_id;
    $this->betted = 0;
    $this->max_bet = 0;
    $this->save();
    // return true;
  }
  public function dealPreflop(){
    $players = $this->game->players;
    $preflop = $this->generateDeck();
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