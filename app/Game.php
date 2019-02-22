<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use App\Player;

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

  public function playersArray($id, $phase){
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
