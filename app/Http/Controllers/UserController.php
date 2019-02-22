<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;


class UserController extends Controller
{
    public function index(Request $request)
    {
    	$data = $request->user();
        return view('users');
    }
    public function loadusers(Request $request){
    	$users = User::all();
    	$online = array();
    	foreach ($users as $user) {
    		if ($user->isOnline() and $user->player->game_id == 0){
    			array_push($online, $user);
    		}
    	}
    	return $online;
    }
}
