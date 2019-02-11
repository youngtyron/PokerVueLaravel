<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DeskCommonEvent;

class GameController extends Controller
{
    public function index(Request $request){
      return view('desk', ['match_id'=>$request->user()->player->game->id, 'gamer_id'=>$request->user()->player->id]);
    }
    public function loadgame(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $game->round->playersArrangement();
      $playersArr = $game->playersArray();
      $gameArr = array('game' => array('phase' => $game->round->phase,
                                       'bank' => $game->round->bank,
                                       'id'=>$game->id),
                       'players'=>$playersArr);
      return $gameArr;
    }
    public function bet(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $player->money = $player->money - $request->input('bet');
      $player->save();
      $game->round->bank = $game->round->bank + $request->input('bet');
      $game->round->save();
      $data =  array('game' => array('phase' => $game->round->phase,
                                   'bank' => $game->round->bank,
                                   'id'=>$game->id),
                    'players'=>$game->playersArray(),
                    'match_id'=>$game->id);
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
}
