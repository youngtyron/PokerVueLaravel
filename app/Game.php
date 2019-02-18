<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{

  public function players()
  {
    return $this->hasMany('App\Player');
  }
  public function round()
  {
    return $this->hasOne('App\Round');
  }
  public function playersArray($id, $phase){
    $players = $this->players;
    $arr = array();
    foreach ($players as $player) {
      $exemplar = array('id' => $player->user->id,
                        'name'=>$player->user->name,
                        'money'=>$player->money,
                        'passing'=>$player->passing);
      if ($player->hand){
        if ($player->id == $id){
          if ($player->hand->first_card){$exemplar += ['first_card'=>'/cards/'.$player->hand->first_card.'.png'];};
          if ($player->hand->second_card){$exemplar += ['second_card'=>'/cards/'.$player->hand->second_card.'.png'];};
        }
        else {
          if ($player->hand->first_card){$exemplar += ['first_card'=>'hidden'];};
          if ($player->hand->second_card){$exemplar += ['second_card'=>'hidden'];};
        }
      } 
      if ($phase=='shotdown'){
        $exemplar += ['combination'=>$player->hand->name_of_combination($player->hand->combination())];
      };
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
