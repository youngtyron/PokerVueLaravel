<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(){
      return view('desk');
    }
    public function loadgame(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $players = $game->players;
      // $seatArray = $game->round->playersArrangement();
      $game->round->playersArrangement();
      $arr = array();
      foreach ($players as $player) {
        if($player->user_id == $request->user()->id){
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'me'=>true,
                            'hand'=>array(),
                            'money'=>$player->money);
        }
        else{
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'hand'=>array(),
                            'money'=>$player->money);
        }
        if ($player->id == $game->round->button_id){$exemplar += ['button'=>true];};
        if ($player->id == $game->round->small_blind_id){$exemplar += ['small_blind'=>true];};
        if ($player->id == $game->round->big_blind_id){$exemplar += ['big_blind'=>true];};

        array_push($arr, $exemplar);
      }
      $gameArr = array('game' => array('phase' => $game->round->phase,
                                       'bank' => $game->round->bank,
                                       'id'=>$game->id),
                       'players'=>$arr);
      return $gameArr;
    }
    public function bet(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $player->money = $player->money - $request->input('bet');
      $player->save();
      $game->round->bank = $game->round->bank + $request->input('bet');
      $game->round->save();
      return array('game' => array('phase' => $game->round->phase,
                                   'bank' => $game->round->bank,
                                   'id'=>$game->id),
                   'player'=>array('id' => $player->user->id,
                                   'name'=>$player->user->name,
                                   'money'=>$player->money));
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
}
