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
    public function findgame(Request $request){
      $player = $request->user()->player;
      if ($player->game_id == Null){
        return view('findgame');
      }
      else{
        return redirect()->route('game');
      }
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
      $player = $request->user()->player;
      if ($player->game_id != Null){
        return view('desk', ['match_id'=>$request->user()->player->game->id, 'gamer_id'=>$request->user()->player->id]);
      }
      else{
        return redirect()->route('findgame');
      }
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
    public function loadgame(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $start = false;
      $loosers = $game->excludeLoosers();


      $round = $game->round;
      if ($round->phase ==  'shotdown'){
        $gameArr = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$player->id, 'match_id'=>$game->id);
      }
      else{
        if ($round->phase == 'blind-bets'){
          $round->settleQueue();
          $small_blind = Player::find($game->round->small_blind_id);
          $small_blind->money = $small_blind->money - 5;
          $small_blind->save();
          $big_blind = Player::find($game->round->big_blind_id);
          $big_blind->money = $big_blind->money - 10;
          $big_blind->save();
          $game->round->bank = 15;
          $game->round->save();
          $game->round->dealPreflop();
          $start = true;
        }
        $playersArr = $game->playersArray($player->id);
        $gameArr = array('game' => $game->gameArray(),
                         'opponents'=>$playersArr,
                         'turn'=>$game->round->current_player_id,
                         'community'=>$game->communityArray(),
                         'player'=>$game->my_playerArray($player->id, $round->phase),
                         'loosers'=>$loosers,
                          'start'=>$start);
      }

      return $gameArr;
    }
    public function fold(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $player->passing = 1;
      $player->save();
      $message = $player->user()->first()->name. ' '. $player->user()->first()->last_name. ' folded!';
      $round = $game->round;
      if ($player->last_bet==Null){
        $round->betted +=1;
      }
      $round->save();
      $players = $round->players();
      if (count($players)==1){
        $round->writeCache();
        foreach ($game->players as $p){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id, 'message'=>$message);
          event(new DeskCommonEvent($data));
        }
        return $data;
      }
      else{
        $next = $round->nextMover($current_index);
        if (is_null($next)){
          $round->nextStep();
          $next = $players->find($round->small_blind_id);
          $minimum = false;
        }
        else{
          $round->current_player_id = $next->id;
          $round->save();
          if ($next->last_bet<$round->max_bet){
            $minimum = $round->max_bet - $next->last_bet;
          }
          if ($round->phase=='shutdown'){
            $round->writeCache();
            foreach ($game->players as $p){
              $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
              event(new DeskCommonEvent($data));
            }
          }
          else{
            $communityarr = $game->communityArray();
            $gamearr = $game->gameArray();
            foreach ($game->players as $p){
              $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($p->id, $round->phase),
                'opponents'=>$game->playersArray($p->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$p->id, 'loosers'=>$loosers);
              if ($p == $next){
                $data += ['next'=>true, 'minimum'=>$minimum];
              }
              event(new DeskCommonEvent($data));
            }
            $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($player->id, $round->phase),
                'opponents'=>$game->playersArray($player->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$player->id, 'loosers'=>$loosers);
            return $data;
          }
        }
      }
    }
    public function bet(Request $request){
      $bet = $request->input('bet');
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      $round->registerBet($player, $bet);
      $loosers = $game->excludeLoosers();
      $players = $round->players();
      if (count($players)==1){
        $round->writeCache();
        foreach ($game->players as $p){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
          event(new DeskCommonEvent($data));
        }
        return $data;
      }
      else{
        $current = $players->find($round->current_player_id);
        $next = $round->nextMover($current);
        if (is_null($next)){
          $round->nextStep();
          $next = $players->find($round->small_blind_id);
          $minimum = false;
        }
        else{
          $round->current_player_id = $next->id;
          $round->save();
          if ($next->last_bet<$round->max_bet){
            $minimum = $round->max_bet - $next->last_bet;
          }
          else{
            $minimum = false;
          }
        }
        if ($round->phase=='shotdown'){
          $round->writeCache();
          foreach ($game->players as $p){
            $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
            event(new DeskCommonEvent($data));
          }
        }
        else{
          if ($bet>$round->max_bet and $round->max_bet!=0){
            $message = $player->user->name. ' '.$player->user->last_name. ' raises to '. (string)$bet. '!';
          }
          else if ($round->max_bet==0){
            $message = $player->user->name. ' '.$player->user->last_name. ' bets '. (string)$bet. '!';
          }
          else{
            $message = $player->user->name. ' '.$player->user->last_name. ' calls with '. (string)$bet. '!';
          }
          $communityarr = $game->communityArray();
          $gamearr = $game->gameArray();
          foreach ($game->players as $p){
              $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($p->id, $round->phase),
                'opponents'=>$game->playersArray($p->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$p->id, 'loosers'=>$loosers);
              if ($p == $next){
                $data += ['next'=>true, 'minimum'=>$minimum];
              }
              event(new DeskCommonEvent($data));
          }
          $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($player->id, $round->phase),
                'opponents'=>$game->playersArray($player->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$player->id, 'loosers'=>$loosers);
          return $data;
        }
      }
    }

    public function nextround(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $round = $game->round;
      if ($round->phase == 'shotdown'){
        $winners = Player::find($round->winner());
        if (count($winners)==1){
          $winner = $winners[0];
          $winner->money = $winner->money + $round->bank;
          $winner->save();
        }
        else{
          $share = (int)($round->bank/count($winners));
          foreach ($winners as $winner) {
            $winner->money = $winner->money+$share;
            $winner->save();
          }
        }
        $round->delete();
        $players = $game->players;
        foreach ($players as $p) {
          $p->hand->delete();
          $p->passing = 0;
          $p->last_bet = Null;
          $p->save();
        }
        $newround = new Round(array('game_id'=>$game->id));
        $newround->save();
      }
    }
    public function leaveGame(Request $request){
      $player = $request->user()->player;
      $player->game_id = Null;
      $player->save();
    }
}
