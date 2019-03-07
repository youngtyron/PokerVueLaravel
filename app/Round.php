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
    if ($this->betted < count($this->players())){
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
  // public function prepareToCompare($id_array){
  //   $array = array();
  //   foreach ($id_array as $id) {
  //     $player = Player::find($id);
  //     array_push($array, array('player' => $id, 'hand'=>$player->hand));
  //   }
  //   return $array;
  // }
  // public function maxCardOwners($handsarray){
  //   $max_array = array();
  //   $extendedArray = array();
  //   foreach ($handsarray as $hand) {
  //     $cards = $player->hand->allcards_array();
  //     $ranks = $hand['hand'];
  //     $max = $hand['hand']->highestCard($ranks);
  //     array_push($extendedArray, array('player'=>$hand['player'], 'max'=>$max, 'hand'=>$hand['hand']));
  //     array_push($max_array, $max);
  //   }
  //   $max = max($max_array);
  //   $search_array = array();
  //   foreach ($extendedArray as $hand) {
  //     if ($hand['max']==$max){
  //       array_push($search_array, $hand);
  //     }
  //   }
  //   return $search_array;
  // }
  // public function compareHands($compareArr){
  //   $preps = $this->prepareToCompare($compareArr);
  //   while (true) {
  //     $compared = $this->maxCardOwners($preps);
  //     if (count($compared)==1){
  //       $winner = $compared[0]['player'];
  //       break;
  //     }
  //     else{
  //       foreach ($preps as $prep) {
  //         $max = max($prep['hand']);
  //         $hand = $prep['hand'];
  //         $index = array_search($max, $hand);
  //         array_slice($hand, $index, 1);
  //         $prep['hand']=$hand;
  //       }
  //     }
  //   }

  //   return $winner;
  // }
  public function maxHands($handsArr){
    $i = 0;
    while ($i<7){
      $max = 0;
      $maxers = array();
      foreach ($handsArr as $hand) {
        $highest = $hand->highestCard($i);
        if ($highest>$max){
          $max  = $highest;
          $maxers = array();
          array_push($maxers, $hand);
        }
        else if ($highest==$max){
          array_push($maxers, $hand);
        }
      }
      if (count($maxers)==1){
        break;
      }
      else{
        $handsArr = $maxers;
        $i += 1;
      }
    }
    return $maxers;
  }
  public function compareHands($compareArr){
    $handsArr = array();
    foreach ($compareArr as $item) {
      $player = $this->players()->find($item);
      array_push($handsArr, $player->hand);
    }
    $maxHands = $this->maxHands($handsArr);
    $winners = array();
    foreach ($maxHands as $h) {
      array_push($winners, $h->player_id);
    }
    return $winners;
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
      array_push($combinations, array('player'=>$player->id, 'rate'=>$player->hand->combination()));
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
        array_slice($combinations, 2, 1);
        $compareArr = array();
        for ($i=0; $i < count($combinations)-1; $i++) { 
          if ($combinations[$i]['rate']==$max){
            array_push($compareArr, $combinations[$i]['player']);
          }
        }
        if (count($compareArr)==1){
          $winner = $compareArr[0];
        }
        else{
          $winner = $this->compareHands($compareArr);
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
  public function nextMover($current){
    $players = $this->game->players;
    $ex_turn = $current->turn;
    $next = Null;
    if ($ex_turn==count($players)){
      $turn = 1;
    }
    else{
      $turn = $ex_turn + 1;
    }
    for ($i=0; $i < count($players); $i++) { 
      $player = Player::where('game_id', $this->game_id)->where('turn', $turn)->first();
      if ($player->passing == 0 and ($player->last_bet == Null or $player->last_bet<$this->max_bet)){
        $next = $player;
        break;
      }
      else{
        if ($turn+1==count($players)){
          $turn = 1;
        }
        else{
          $turn +=1;
        }
      }
    }
    return $next;
  }

  public function nextStep(){
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
    foreach ($this->players() as $p){
      $p->last_bet = null;
      $p->save();
    }
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
    $this->save();
    return true;
  }
  public function settleQueue(){
    $players = $this->game->players;
    if ($players[0]->turn == Null){
      $button = $players[array_rand($players->toArray(), 1)];
      $index = array_search($button, $players->all());
      $this->button_id = $button->id;
      $this->save();
      $turn = 1;
      while ($turn <= count($players)){
        if ($index+1 == count($players)){
          $index = 0;
        }
        else{
          $index = $index+1;
        }
        $player = $players[$index];
        $player->turn = $turn;
        $player->save();
        if($turn == 1){
          $this->small_blind_id = $player->id;
        }
        else if ($turn == 2){
          $this->big_blind_id = $player->id;
        }
        if (count($players)>2){
          $third = $players->where('turn', 3)->first();
          $this->current_player_id = $third->id;
        }
        else{
          $this->current_player_id = $this->small_blind_id;
        }
        $this->save();
        $turn +=1;
      }
    }
    else{
      $this->shiftQueue($players);
    }
  }  
  public function shiftQueue($players){
    foreach ($players as $player) {
      if ($player->turn == count($players)){
        $player->turn = 1;
        $player->save();
        $this->small_blind_id = $player->id;
      }
      else{
        $player->turn = $player->turn + 1;
        $player->save();
        if ($player->turn==2){
          $this->big_blind_id = $player->id;
        }
        else if ($player->turn == count($players)){
          $this->button_id = $player->id;
        }
      }
    }
    if (count($players)>2){
      $third = $players->where('turn', 3)->first();
      $this->current_player_id = $third->id;
    }
    else{
      $this->current_player_id = $this->small_blind_id;
    }
    $this->save();
  }
}