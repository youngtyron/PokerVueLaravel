<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
Route::get('/users', 'UserController@index')->name('users');
Route::get('/loadusers', 'UserController@loadusers');

Route::get('/game', 'GameController@index')->name('game');
Route::get('/loadgame', 'GameController@loadgame');
Route::post('/blinds', 'GameController@blinds');
Route::post('/bet', 'GameController@bet');
Route::post('/pass', 'GameController@pass');




