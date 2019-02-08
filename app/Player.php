<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
  protected $guarded = [];
  public $timestamps = false;

  public function user()
  {
     return $this->belongsTo('App\User');
  }
  public function game()
  {
    return $this->belongsTo('App\Game');
  }
  // public function hand(){
  //   return 'hand';
  // }
}
