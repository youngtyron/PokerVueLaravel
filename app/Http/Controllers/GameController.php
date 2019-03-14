<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\DeskCommonEvent;
use App\Player;
use App\Game;
use App\Round;
use Cache;


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
        $round->active = 1;
        $round->save();
        return response()->json(['message' => 'ok'], 200);
      }
      else{
        return response()->json(['message' => 'not'], 200);
      }
    }
    public function leave_game(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $turn = $player->turn;
      $player->game_id = Null;
      $player->last_bet = Null;
      $player->passing = 0;
      $player->turn = Null;
      $player->search_number_players = Null;
      $player->save();
      $round=$game->round;
      $oldplayers = $game->players;
      $newplayers = $game->players->where('id', '!=', $player->id);
      if (count($newplayers->where('passing', 0))==1){
        $message = false;
        $round->writeCache();
        foreach ($newplayers as $p){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id, 'message'=>$message);
          event(new DeskCommonEvent($data));
        }
        //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
        $game->moneyToWinner();
        $game->deleteRound();
        return;
      }
      if ($player->id == $round->current_player_id){
        $next = $round->nextMover($player);
        if (is_null($next)){
          $round->nextStep();
          $next = $round->smallBlindIfHePlays();
          $round->current_player_id = $next->id;
          $round->save();
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
      }
      else{
        $next = Null;
      }
      if ($turn<count($oldplayers)){
        $shifted = $newplayers->where('turn', '>', $turn);
        foreach ($shifted as $sh) {
          $sh->turn -= 1;
          $sh->save();
        }
      }
      $message = $player->user->name. ' '. $player->user->last_name. ' left the game!';
      if ($round->phase=='shutdown'){
        $round->writeCache();
        foreach ($newplayers as $p){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
          event(new DeskCommonEvent($data));
        }
        //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
        $game->moneyToWinner();
        $game->deleteRound();
        return;
      }
      else{
        $communityarr = $game->communityArray();
        $gamearr = $game->gameArray();
        foreach ($newplayers as $p){
          $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($p->id, $round->phase),
            'opponents'=>$game->playersArray($p->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
            'gamer'=>$p->id);
          if ($p == $next){
            $data += ['next'=>true, 'minimum'=>$minimum];
          }
          event(new DeskCommonEvent($data));
        }
        return;
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
      if ($round->active==1){
        if ($round->phase ==  'shotdown'){
          $gameArr = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$player->id, 'match_id'=>$game->id);
        }
        else if (count($game->players)==1){
          $game->moneyToWinner();
          $data = array('game_end'=>true, 'player'=>$game->players[0]->infoArray() ,'gamer'=>$game->players[0]->id, 
                        'match_id'=>$game->id);
          return $data;
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
      }
      else{
        $gameArr = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$player->id, 'match_id'=>$game->id);
      }

      return $gameArr;
    }
    public function fold(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $current = $player;
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
        //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
        $game->moneyToWinner();
        $game->deleteRound();
        return $data;
      }
      else{
        $next = $round->nextMover($current);
        if (is_null($next)){
          $round->nextStep();
          // $next = $players->find($round->small_blind_id);
          $next = $round->smallBlindIfHePlays();
          $round->current_player_id = $next->id;
          $round->save();
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
          if ($round->phase=='shutdown'){
            $round->writeCache();
            foreach ($game->players as $p){
              $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
              event(new DeskCommonEvent($data));
            }
            //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
            $game->moneyToWinner();
            $game->deleteRound();
            return $data;
          }
          else{
            $communityarr = $game->communityArray();
            $gamearr = $game->gameArray();
            foreach ($game->players as $p){
              $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($p->id, $round->phase),
                'opponents'=>$game->playersArray($p->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$p->id);
              if ($p == $next){
                $data += ['next'=>true, 'minimum'=>$minimum];
              }
              event(new DeskCommonEvent($data));
            }
            $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($player->id, $round->phase),
                'opponents'=>$game->playersArray($player->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$player->id);
            return $data;
          }
        }
      }
    }
    public function bet(Request $request){
      $bet = $request->input('bet');
      $player = $request->user()->player;
      $g = $player->game;
      $round = $g->round;
      $round->registerBet($player, $bet);
      $loosers = $g->excludeLoosers();
      $players = $round->players();
      $game=$round->game;
      if ($loosers!=false){
        foreach ($loosers as $looser) {
          $data = array('you_lose'=>true, 'gamer'=>$looser->id, 'match_id'=>$game->id);
          event(new DeskCommonEvent($data));
        }
      }

      if (count($game->players)==1){
        $game->moneyToWinner();
        foreach ($game->players as $p){
          $data = array('game_end'=>true, 'player'=>$p->infoArray() ,'gamer'=>$p->id, 'match_id'=>$game->id);
          event(new DeskCommonEvent($data));
        }
        if ($player == $game->players[0]){
          return $data;
        }
        else{
          return;
        }
      }
      else if (count($players)==1){
        $round->writeCache();
        foreach ($game->players as $p){
          $data = array('end'=>true, 'results'=>$game->returnCache(), 'gamer'=>$p->id, 'match_id'=>$game->id);
          event(new DeskCommonEvent($data));
        }

        //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
        $game->moneyToWinner();
        $game->deleteRound();
        return $data;
      }
      else{
        $current = $player;
        // $current = $players->find($round->current_player_id);
        $next = $round->nextMover($current);
        if (is_null($next)){
          $round->nextStep();
          // $next = $players->find($round->small_blind_id);
          $next = $round->smallBlindIfHePlays();
          $round->current_player_id = $next->id;
          $round->save();
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
          //ОЧИСТКА РАУНДА ПОСЛЕ ДЕЙСТВИЯ ИГРОКА И ПОДСЧЕТА РЕЗУЛЬТАТА
          $game->moneyToWinner();
          $game->deleteRound();
          return $data;
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
          if ($loosers!=false){
            if (in_array($player, $loosers)){
              $data = array('you_lose'=>true, 'gamer'=>$player->id, 'match_id'=>$game->id);
            }  
          }
          else{
            $data = array('game'=>$gamearr, 'player'=> $game->my_playerArray($player->id, $round->phase),
                'opponents'=>$game->playersArray($player->id), 'match_id'=>$game->id, 'community'=>$communityarr, 'message'=>$message, 
                'gamer'=>$player->id, 'loosers'=>$loosers); 
          }      
          return $data;
        }
      }
    }

    public function nextround(Request $request){
      $player = $request->user()->player;
      $game = $player->game;
      $game->round->active = 1;
      $game->round->save();
      Cache::forget('result.'.$game->id);
    }
    public function leaveGame(Request $request){
      $player = $request->user()->player;
      $player->game_id = Null;
      $player->save();
    }
}
