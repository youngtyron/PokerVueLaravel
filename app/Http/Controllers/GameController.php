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
      if ($round->phase == 'flop' or $round->phase == 'turn' or $round->phase == 'river'){
        $communityarr = array('first_card' => '/cards/'.$round->first_card.'.png', 'second_card'=>'/cards/'.$round->second_card.'.png', 'third_card'=>'/cards/'.$round->third_card.'.png');
      }
      else{
        $communityarr = null; 
      }
      $gameArr = array('game' => array('phase' => $round->phase,
                                       'bank' => $round->bank,
                                       'id'=>$game->id),
                       'players'=>$playersArr,
                       'turn'=>$game->round->current_player_id,
                       'community'=>$communityarr);

      return $gameArr;
    }
    public function bet(Request $request){
      $bet = $request->input('bet');
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      $players = $game->players;
      $player->money = $player->money - $bet;
      $player->last_bet = $player->last_bet+$bet;
      $player->save();
      $round->bank = $round->bank + $bet;
      if ($player->last_bet> $round->max_bet){
        $round->max_bet = $player->last_bet+$bet;
      }
      if ($round->betted+1 >= count($players)){
        $turn_player = $round->whoMustCallNext();
        if (is_null($turn_player)){
          $call = null;
          $round->phase = 'flop';
          $round->dealFlop();
          $round->current_player_id = $round->small_blind_id;
          $round->betted = 0;
          $round->max_bet = 0;
          foreach ($players as $p){
            $p->last_bet = null;
            $p->save();
          }
          $turn = $game->round->current_player_id;
        }
        else {
          $call = $round->max_bet - $turn_player->last_bet;
          $turn = $turn_player->id;
          $round->current_player_id = $turn_player->id;
          if ($round->betted <count($players)){
            $round->betted = $game->round->betted+1;
          }
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
        $round->betted = $game->round->betted+1;
        $call = null;
        $turn = $game->round->current_player_id;
      }
      $round->save();
      $data =  array('game' => array('phase' => $round->phase,
                                   'bank' => $round->bank,
                                   'id'=>$game->id),
                    'players'=>$game->playersArray(),
                    'match_id'=>$game->id,
                    'turn'=>$turn,
                    'call'=>$call,
                    'community'=>$round->communityArray()
                  );
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
                            'money'=>$player->money);
        }
        else{
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'first_card'=>'/cards/'.$player->hand->first_card.'.png',
                            'second_card'=>'/cards/'.$player->hand->second_card.'.png',
                            'money'=>$player->money);
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
      $data =  array('game' => array('phase' => $game->round->phase,
                                   'bank' => $game->round->bank,
                                   'id'=>$game->id),
                    'players'=>$playersarr,
                    'match_id'=>$game->id,
                    'other'=>'blinds_done');
       event(new DeskCommonEvent($data));
       return $data;
    }
}
