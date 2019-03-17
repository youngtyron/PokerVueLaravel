<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BaseUrlTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFirstPage()
    {
    	$response = $this->get('/');
        $response->assertStatus(200);
    }
    public function testLoginPage()
    {
    	$response = $this->get('/login');
        $response->assertStatus(200);
    }
    public function testRegisterPage()
    {
    	$response = $this->get('/register');
        $response->assertStatus(200);
    }
    public function testGamePageAnonymous(){
    	$response = $this->get('/game');
    	$response->assertRedirect('login');
    }
    public function testFindGamePageAutorized(){
    	$user = factory(\App\User::class)->create();
    	$player = \App\Player::create(['user_id'=>$user->id]);
    	$response = $this->actingAs($user)
    					 ->get('/findgame');
    	$response->assertStatus(200);
    }
}



