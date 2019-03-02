<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DeskCommonEvent;
use App\Player;
use App\Game;
use App\Round;


class GameController extends Controller
{
    public function __construct()
    {
       $this->middleware('auth');
    }
    public function findgame(){
      return view('findgame');
    }
    public function search_game(Request $request){
      $player = $request->user()->player;
      $num = $request->input('num');
      if ($num !=0){
        $partners = $player->definite_partners_amount($num);
      }
      else{
        $partners = $player->any_partners_amount();
      }
      if ($partners){
        $game = new Game;
        $game->save();
        foreach ($partners as $partner) {
          $partner->game_id = $game->id;
          $partner->search_number_players = Null;
          $partner->save();
        }
        $round=Round::create(['game_id'=>$game->id]);
        $round->save();
        return response()->json(['message' => 'ok'], 200);
      }
      else{
        return response()->json(['message' => 'not'], 200);
      }
    }
    public function index(Request $request){
      return view('desk', ['match_id'=>$request->user()->player->game->id, 'gamer_id'=>$request->user()->player->id]);
    }
    public function loadgame(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      if ($round->phase ==  'shotdown'){
        $gameArr = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$player->id, 'match_id'=>$game->id);
      }
      else{
        $round->playersArrangement();
        if ($round->phase == 'blind-bets'){
          $round->blinds();
        }
        $playersArr = $game->playersArray($player->id, $round->phase);
        $gameArr = array('game' => $game->gameArray(),
                         'opponents'=>$playersArr,
                         'turn'=>$game->round->current_player_id,
                         'community'=>$game->communityArray(),
                         'player'=>$game->my_playerArray($player->id, $round->phase));
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
      if ($round->phase == 'shotdown'){
        $round->writeCache();
      }
      foreach ($game->players as $p) {
        if ($round->phase == 'shotdown'){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
        }
        else{
          $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($p->id, $round->phase),
                        'opponents'=>$game->playersArray($p->id, $round->phase), 'match_id'=>$game->id, 
                        'turn'=>$turn, 'call'=>$call_required, 'community'=>$communityarr, 'message'=>$message, 
                        'bet_type'=>$bet_type, 'previous'=>$previous, 'gamer'=>$p->id);
        }
        event(new DeskCommonEvent($data));
      }
      if ($round->phase == 'shotdown'){
        $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
        $winner = Player::find($round->winner());
        $winner->money = $winner->money + $round->bank;
        $winner->save();
        $round->delete();
        $gameplayers = $game->players;
        foreach ($gameplayers as $p) {
          $p->hand->delete();
          $p->passing = 0;
          $p->last_bet = Null;
          $p->save();
        }
        $newround = new Round(array('game_id'=>$game->id));
        $newround->save();
      }
      else{
        $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($player->id, $round->phase),
                        'opponents'=>$game->playersArray($player->id, $round->phase), 'match_id'=>$game->id, 
                        'turn'=>$turn, 'call'=>$call_required, 'community'=>$communityarr, 'message'=>$message, 
                        'bet_type'=>$bet_type, 'previous'=>$previous, 'gamer'=>$p->id);
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
    public function nextround(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      if ($round->phase == 'shotdown'){
        $winner = Player::find($round->winner());
        $winner->money = $winner->money + $round->bank;
        $winner->save();
        $round->delete();
        $players = $game->players();
        foreach ($players as $p) {
          $p->hand->delete();
          $p->passing = 0;
          $p->last_bet = Null;
          $p->save();
        }
        $newround = new Round(array('game_id'=>$game->id));
        $newround->save();
      }
      return 'true';
    }
}
