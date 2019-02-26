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
  public function definite_partners_amount($num){
    $this->search_number_players = $num;
    $this->save();
    $free_gamers = Player::where('game_id', Null);
    $i = 0;
    while ($i <= 5) {
      $partners=$free_gamers->where('search_number_players', $num)
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
    $free_gamers = Player::where('game_id', Null);
    $partners = false;
    $i = 0;
    while ($i <= 5) {
      $n_arr = [2, 4, 6, 8];
      while (count($n_arr)>0){
        $variant = $n_arr[array_rand($n_arr)];
        $pre_partners=$free_gamers->where('search_number_players', $variant)
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


    return $partners;

    // if ($partners){
    //   return $partners;
    // }
    // else{
    //   return false;
    // }
  }
}
