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
      $arr = array();
      foreach ($players as $player) {
        if($player->user_id == $request->user()->id){
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'me'=>true,
                            'hand'=>array());
        }
        else{
          $exemplar = array('id' => $player->user->id,
                            'name'=>$player->user->name,
                            'hand'=>array());
        }
        array_push($arr, $exemplar);
      }
      $gameArr = array('game' => $game->phase, 'players'=>$arr);
      return $gameArr;
    }
}
