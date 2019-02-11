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

Route::get('/game', 'GameController@index')->name('game');

Route::group(['prefix' => 'api'], function()
{
  Route::get('/loadgame', 'GameController@loadgame');
  Route::get('/dealpreflop', 'GameController@dealPreflop');

  // Route::post('/bet', 'GameController@bet');
});

Route::get('/test', function () {
  return 'Hello World';
});

Route::post('/bet', 'GameController@bet');
