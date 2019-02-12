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
  public function playersArray(){
    $players = $this->players;
    $arr = array();
    foreach ($players as $player) {
      $exemplar = array('id' => $player->user->id,
                        'name'=>$player->user->name,
                        'money'=>$player->money);
      if ($player->hand->first_card){$exemplar += ['first_card'=>'/cards/'.$player->hand->first_card.'.png'];};
      if ($player->hand->second_card){$exemplar += ['second_card'=>'/cards/'.$player->hand->second_card.'.png'];};

      if ($player->id == $this->round->button_id){$exemplar += ['button'=>true];};
      if ($player->id == $this->round->small_blind_id){$exemplar += ['small_blind'=>true];};
      if ($player->id == $this->round->big_blind_id){$exemplar += ['big_blind'=>true];};
      if ($player->id == $this->round->current_player_id){$exemplar += ['current'=>true];};
      array_push($arr, $exemplar);
    }
    return $arr;
  }
}
