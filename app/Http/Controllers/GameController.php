<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DeskCommonEvent;
use App\Player;

class GameController extends Controller
{
    public function index(Request $request){
      return view('desk', ['match_id'=>$request->user()->player->game->id, 'gamer_id'=>$request->user()->player->id]);
    }
    public function loadgame(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      $round->playersArrangement();
      $playersArr = $game->playersArray($player->id, $round->phase);
      $gameArr = array('game' => $game->gameArray(),
                       'players'=>$playersArr,
                       'turn'=>$game->round->current_player_id,
                       'community'=>$game->communityArray(),
                       'me'=>$game->my_playerArray($player->id, $round->phase));
      if ($round->phase == 'shotdown'){
        $gameArr += ['winner'=>$round->winner()];
      }
      return $gameArr;
    }
    public function pass(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $player->passing = 1;
      $player->save();
      $round = $game->round;
      $players = $round->players();
      if ($round->betted>= count($players)){
        $winner = $round->winner();
        if ($winner){
          $answer = 'Winner is: ' . $winner->user->name;
        }
        else{
          $turn_player = $round->whoMustCallNext();
          if (is_null($turn_player)){
            $answer = 'Game continues, next round';
          }
          else {
            $turn = $turn_player->id;
            $round->current_player_id = $turn_player->id;
            $call = $round->max_bet - $turn_player->last_bet;
            $answer = 'Game continues, '.$turn_player->user->name . ' should bet ' . $call;
          }
        }
      }
      else{
        $answer = 'Game continues, this round';
      }
      $round->save();
      return $answer;
    }
    public function bet(Request $request){
      $bet = $request->input('bet');
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      $players = $round->players();
      $player->money = $player->money - $bet;
      $player->last_bet = $player->last_bet+$bet;
      $player->save();
      if ($bet>$round->max_bet and $round->max_bet!=0){
        $bet_type = 'raise';
        $bet_amount = $player->last_bet;
      }
      else if ($round->max_bet==0){
        $bet_type = 'bet';
        $bet_amount = $bet;
      }
      else{
        $bet_type = 'call';
        $bet_amount = $bet;
      }
      $round->bank = $round->bank + $bet;
      if ($player->last_bet> $round->max_bet){
        $round->max_bet = $player->last_bet;
      }
      $round->save();
      if ($round->betted+1 >= count($players)){
        $turn_player = $round->whoMustCallNext();
        if (is_null($turn_player)){
          $call_required = null;
          $round->nextStep();
          $turn = $round->current_player_id;
          $message = "Your turn to bet";
        }
        else {
          $call_required = $round->max_bet - $turn_player->last_bet;
          $turn = $turn_player->id;
          $round->current_player_id = $turn_player->id;
          if ($round->betted < count($players)){
            $round->betted = $game->round->betted+1;
          }
          $round->save();
          $message = "You have to call with " . $call_required . " more";
        }
      }
      else{
        foreach ($players as $p) {
          if ($p->id == $player->id){
            $current_index = array_search($p, $players->all());
          }
        }
        if ($current_index+1 == count($players)){
          $new_index = 0;
        }
        else{
          $new_index = $current_index+1;
        }
        $round->current_player_id = $players[$new_index]->id;
        $round->betted = $round->betted+1;
        $call_required = $player->last_bet;
        $round->save();
        if ($round->betted == 1){
          $message = $player->user->name . ' bets ' . $player->last_bet; 
        }
        else {
          $message = $player->user->name . ' calls with ' . $player->last_bet;
        }
        $turn = $game->round->current_player_id;
      }
      $communityarr = $game->communityArray();
      $gamearr = $game->gameArray();
      $previous = array('name'=>$player->user->name, 'bet'=>$bet_amount);
      foreach ($players as $p) {
        $data = array('game'=>$gamearr, 'players'=>$game->playersArray($p->id, $round->phase), 'match_id'=>$game->id, 
                      'turn'=>$turn, 'call'=>$call_required, 'community'=>$communityarr, 'message'=>$message, 
                      'bet_type'=>$bet_type, 'previous'=>$previous, 'gamer'=>$p->id);
        if ($round->phase == 'shotdown'){
          $data += ['winner'=>$round->winner()];
        }
        event(new DeskCommonEvent($data));
      }
      return $data;
    }
    public function blinds(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $small_blind = Player::find($game->round->small_blind_id);
      $small_blind->money = $small_blind->money - 5;
      $small_blind->save();
      $big_blind = Player::find($game->round->big_blind_id);
      $big_blind->money = $big_blind->money - 10;
      $big_blind->save();
      $game->round->bank = 15;
      $game->round->save();
      $game->round->dealPreflop();
      foreach ($game->players as $p) {
        $data = array('game'=>$gamearr, 'players'=>$game->playersArray($p->id, $game->round->phase), 'match_id'=>$game->id, 'gamer'=>$p->id);
        event(new DeskCommonEvent($data));
      }
      return $data;
    }
}
