<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Player;
use App\Round;
use Cache;


class Game extends Model
{

  public function players()
  {
    return $this->hasMany('App\Player');
  }
  public function opponents($id){
    return Player::where('game_id', $this->id)->where('id', '!=', $id)->get();
  }
  public function round()
  {
    return $this->hasOne('App\Round');
  }
  public function moneyToWinner(){
    $round=$this->round;
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
  }
  public function deleteRound(){
    $this->round->delete();
    $players = $this->players;
    foreach ($players as $p) {
      $p->hand->delete();
      $p->passing = 0;
      $p->last_bet = Null;
      $p->save();
    }
    $newround = new Round(array('game_id'=>$this->id));
    $newround->save();
  }
  public function loosers(){
    $loosers = array();
    foreach ($this->players as $p) {
      if ($p->money < $this->round->max_bet){
        array_push($loosers, $p);
      }
    }
    return $loosers;
  }
  public function excludeLoosers(){
    $loosers = $this->loosers();
    if (count($loosers)>0){
      foreach ($loosers as $looser) {
        $looser->game_id = Null;
        $looser->save();
      }
      return $loosers;
    }
    else{
      return false;
    }
  }
  public function returnCache(){
    if (Cache::has('result.'.$this->id))
      {
          $value = Cache::get('result.'.$this->id);
          return $value;
      }
    else{
      return false;
    }
  }
  public function my_playerArray($id, $phase){
    $me = Player::find($id);
    $arr = array('id' => $me->user->id,
                  'name'=>$me->user->name,
                  'last_name'=>$me->user->last_name,
                  'money'=>$me->money,
                  'passing'=>$me->passing);

    if ($me->hand){
        if ($me->hand->first_card){$arr += ['first_card'=>'/cards/'.$me->hand->first_card.'.png'];};
        if ($me->hand->second_card){$arr += ['second_card'=>'/cards/'.$me->hand->second_card.'.png'];};
    }

    if ($phase=='shotdown'){
      $arr += ['combination'=>$me->hand->name_of_combination($me->hand->combination())];
    };
    if ($me->id == $this->round->button_id){$arr += ['button'=>true];};
    if ($me->id == $this->round->small_blind_id){$arr += ['small_blind'=>true];};
    if ($me->id == $this->round->big_blind_id){$arr += ['big_blind'=>true];};
    if ($me->id == $this->round->current_player_id){$arr += ['current'=>true];};
    if ($me->last_bet){$arr += ['last_bet'=>$me->last_bet];}
    else {$arr += ['last_bet'=>0];}
    return $arr;
  }
  public function playersArray($id){
    $players = $this->opponents($id);
    $arr = array();
    foreach ($players as $player) {
      $exemplar = array('id' => $player->user->id,
                        'name'=>$player->user->name,
                        'last_name'=>$player->user->last_name,
                        'money'=>$player->money,
                        'passing'=>$player->passing);
      if ($player->hand){
        if ($player->hand->first_card){$exemplar += ['first_card'=>true];};
        if ($player->hand->second_card){$exemplar += ['second_card'=>true];};
      } 
      if ($player->id == $this->round->button_id){$exemplar += ['button'=>true];};
      if ($player->id == $this->round->small_blind_id){$exemplar += ['small_blind'=>true];};
      if ($player->id == $this->round->big_blind_id){$exemplar += ['big_blind'=>true];};
      if ($player->id == $this->round->current_player_id){$exemplar += ['current'=>true];};
      if ($player->last_bet){
        $exemplar += ['last_bet'=>$player->last_bet];
      }
      else {
        $exemplar += ['last_bet'=>0];
      }
      array_push($arr, $exemplar);
    }
    return $arr;
  }
  public function gameArray(){
    $gamearr = array('phase' => $this->round->phase,
                     'bank' => $this->round->bank,
                     'id'=>$this->id,
                     'max_bet'=>$this->round->max_bet);
    return $gamearr;
  }
  public function communityArray(){
    if ($this->round->phase == 'flop'){
      $communityarr = array('first_card' => '/cards/'.$this->round->first_card.'.png', 'second_card'=>'/cards/'.$this->round->second_card.'.png', 'third_card'=>'/cards/'.$this->round->third_card.'.png');
    }
    else if ($this->round->phase == 'turn'){
      $communityarr = array('first_card' => '/cards/'.$this->round->first_card.'.png', 'second_card'=>'/cards/'.$this->round->second_card.'.png', 'third_card'=>'/cards/'.$this->round->third_card.'.png', 'fourth_card'=>'/cards/'.$this->round->fourth_card.'.png');
    }
    else if ($this->round->phase == 'river'){
      $communityarr = array('first_card' => '/cards/'.$this->round->first_card.'.png', 'second_card'=>'/cards/'.$this->round->second_card.'.png', 'third_card'=>'/cards/'.$this->round->third_card.'.png', 'fourth_card'=>'/cards/'.$this->round->fourth_card.'.png', 'fifth_card'=>'/cards/'.$this->round->fifth_card.'.png');
    }
    else if ($this->round->phase == 'shotdown'){
      $communityarr = array('first_card' => '/cards/'.$this->round->first_card.'.png', 'second_card'=>'/cards/'.$this->round->second_card.'.png', 'third_card'=>'/cards/'.$this->round->third_card.'.png', 'fourth_card'=>'/cards/'.$this->round->fourth_card.'.png', 'fifth_card'=>'/cards/'.$this->round->fifth_card.'.png');
    }
    else{
      $communityarr = null; 
    }
    return $communityarr;
  }
}
