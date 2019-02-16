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
      $playersArr = $game->playersArray();
      $gameArr = array('game' => $game->gameArray() ,
                       'players'=>$playersArr,
                       'turn'=>$game->round->current_player_id,
                       'community'=>$game->communityArray());

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
      elseif ($round->max_bet==0){
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
      if ($round->betted+1 >= count($players)){
        $turn_player = $round->whoMustCallNext();
        if (is_null($turn_player)){
          $call = null;
          $round->nextStep();
          $turn = $game->round->current_player_id;
          $message = "You bet";
        }
        else {
          $call = $round->max_bet - $turn_player->last_bet;
          $turn = $turn_player->id;
          $round->current_player_id = $turn_player->id;
          if ($round->betted <count($players)){
            $round->betted = $game->round->betted+1;
          }
          $message = "You have to call with " . $call . " more";
        }
      }
      else{
        $message = $player->user->name . ' bets ' . $player->last_bet; 
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
        $round->betted = $game->round->betted+1;
        $call = null;
        $turn = $game->round->current_player_id;
      }
      $round->save();
      if ($round->phase!='shotdown'){
        $data =  array('game' => $game->gameArray(),
              'players'=>$game->playersArray(),
              'match_id'=>$game->id,
              'turn'=>$turn,
              'call'=>$call,
              'community'=>$game->communityArray(),
              'message'=> $message,
              'bet_type'=>$bet_type,
              'previous'=>array('name'=>$player->user->name, 'bet'=>$bet_amount)
            );
      }
      else{
        $data = array('game' => $game->gameArray(),
              'players'=>$game->playersArray(),
              'match_id'=>$game->id,
              'turn'=>$turn,
              'call'=>$call,
              'community'=>$game->communityArray(),
              'message'=> 'game is over',
              // 'bet_type'=>$bet_type,
              // 'previous'=>array('name'=>$player->user->name, 'bet'=>$bet_amount)
            );
      }

     event(new DeskCommonEvent($data));
     return $data;
    }
    public function dealPreflop(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $game->round->dealPreflop();
      $players = $game->players;
      $arr = array();
      foreach ($players as $player) {
        if($player->user_id == $request->user()->id){
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'me'=>true,
                            'first_card'=>'/cards/'.$player->hand->first_card.'.png',
                            'second_card'=>'/cards/'.$player->hand->second_card.'.png',
                            'money'=>$player->money,
                            'passing'=>$player->passing);
        }
        else{
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'first_card'=>'/cards/'.$player->hand->first_card.'.png',
                            'second_card'=>'/cards/'.$player->hand->second_card.'.png',
                            'money'=>$player->money,
                            'passing'=>$player->passing);
        }
        array_push($arr, $exemplar);
      }
      return $arr;
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
      $game->round->phase = 'preflop';
      $game->round->save();
      $game->round->dealPreflop();
      $playersarr = $game->playersArray();
      $data =  array('game' => $game->gameArray(),
                    'players'=>$playersarr,
                    'match_id'=>$game->id,
                    'other'=>'blinds_done');
       event(new DeskCommonEvent($data));
       return $data;
    }
}
