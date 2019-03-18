<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Player;

class GameSearchTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testTwoGameSearchers(){
        $user = factory(\App\User::class)->create();
        $player = Player::create(['user_id'=>$user->id, 'search_number_players'=>2]);
        $second_user = factory(\App\User::class)->create();
        $second_player = Player::create(['user_id'=>$second_user->id]);

        $this->browse(function ($browser) use ($second_user) {
            $browser->loginAs($second_user)
                    ->visit('/findgame')
                    ->click('#two');
            $browser->visit('/game')
                    ->assertPathIs('/game'); 
        });
    }
    public function testThreeGameSearchers(){
        $users = factory(\App\User::class, 2)->create();
        foreach ($users as $user) {
            $player = Player::create(['user_id'=>$user->id, 'search_number_players'=>3]);
        }
        $third_user = factory(\App\User::class)->create();
        $third_player = Player::create(['user_id'=>$third_user->id]);

        $this->browse(function ($browser) use ($third_user) {
            $browser->loginAs($third_user)
                    ->visit('/findgame')
                    ->click('#three');
            $browser->visit('/game')
                    ->assertPathIs('/game'); 
        });
    }
    public function testFiveGameSearchers(){
        $users = factory(\App\User::class, 4)->create();
        foreach ($users as $user) {
            $player = Player::create(['user_id'=>$user->id, 'search_number_players'=>5]);
        }
        $fifth_user = factory(\App\User::class)->create();
        $fifth_player = Player::create(['user_id'=>$fifth_user->id]);

        $this->browse(function ($browser) use ($fifth_user) {
            $browser->loginAs($fifth_user)
                    ->visit('/findgame')
                    ->click('#five');
            $browser->visit('/game')
                    ->assertPathIs('/game'); 
        });
    }
    public function testSevenGameSearchers(){
        $users = factory(\App\User::class, 6)->create();
        foreach ($users as $user) {
            $player = Player::create(['user_id'=>$user->id, 'search_number_players'=>7]);
        }
        $seventh_user = factory(\App\User::class)->create();
        $seventh_player = Player::create(['user_id'=>$seventh_user->id]);

        $this->browse(function ($browser) use ($seventh_user) {
            $browser->loginAs($seventh_user)
                    ->visit('/findgame')
                    ->click('#seven');
            $browser->visit('/game')
                    ->assertPathIs('/game'); 
        });
    }
}
