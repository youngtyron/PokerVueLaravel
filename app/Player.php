<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
  protected $guarded = [];
  public $timestamps = false;

  public function user(){
     return $this->belongsTo('App\User');
  }
  public function game(){
    return $this->belongsTo('App\Game');
  }
  public function hand(){
    return $this->hasOne('App\Hand');
  }
  public function infoArray(){
    $info = array('id' => $this->user->id,
                  'name'=>$this->user->name,
                  'last_name'=>$this->user->last_name,
                  'money'=>$this->money,
                  'passing'=>$this->passing,
                  'first_card'=>'/cards/'.$this->hand->first_card.'.png',
                  'second_card'=>'/cards/'.$this->hand->second_card.'.png',
                  'combination'=>$this->hand->name_of_combination($this->hand->combination()));
    return $info;
  }
  public function definite_partners_amount($num){
    $this->search_number_players = $num;
    $this->save();
    $i = 0;
    while ($i <= 5) {
      $partners=Player::where('game_id', Null)->where('search_number_players', $num)
                            ->orWhere('search_number_players', 0)
                            ->take($num)->get();
      if (count($partners)<$num){
        sleep(1);
      }
      else{
        break;
      }
      $i +=1;
    }
    if (count($partners)==$num){
      return $partners;
    }
    else{
      return false;
    }
  }
  public function any_partners_amount(){
    $this->search_number_players = 0;
    $this->save();
    $partners = false;
    $i = 0;
    while ($i <= 5) {
      $n_arr = [2, 3, 5, 7];
      while (count($n_arr)>0){
        $variant = $n_arr[array_rand($n_arr)];
        $pre_partners=Player::where('game_id', Null)->where('search_number_players', $variant)
                              ->orWhere('search_number_players', 0)
                              ->take($variant)->get();

        if (count($pre_partners)<$variant){
          array_splice($n_arr, array_search($variant, $n_arr), 1);
        }
        else{
          $partners = $pre_partners;
          break;
        }
      }
      if ($partners){
        break;
      }
      else{
        sleep(1);
      }
      $i +=1;
    }
    if ($partners){
      return $partners;
    }
    else{
      return false;
    }
  }
}
