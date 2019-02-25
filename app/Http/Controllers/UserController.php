<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\FreePlayerEvent;
use App\User;


class UserController extends Controller
{
    public function index(Request $request)
    {
        return view('users');
    }
    public function loadusers(Request $request){
    	$users = User::all();
    	$online = array();
    	foreach ($users as $user) {
    		if ($user->isOnline() and is_null($user->player->game_id)){
    			array_push($online, $user);
    		}
    	}
    	return $online;
    }
}
