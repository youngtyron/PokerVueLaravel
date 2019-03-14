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
Route::get('/findgame', 'GameController@findgame')->name('findgame')->middleware('auth');
Route::get('/game', 'GameController@index')->name('game')->middleware('auth');
Route::get('/loadgame', 'GameController@loadgame')->middleware('auth');
Route::post('/nextround', 'GameController@nextround')->middleware('auth');
Route::post('/blinds', 'GameController@blinds')->middleware('auth');
Route::post('/bet', 'GameController@bet')->middleware('auth');
Route::post('/fold', 'GameController@fold')->middleware('auth');
Route::post('/search', 'GameController@search_game')->middleware('auth');
Route::post('/leave', 'GameController@leave_game')->middleware('auth');



