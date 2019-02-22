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
    	return $users;
    }
}
