<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testAfterLoginRedirect()
    {

        $user = factory(\App\User::class)->create();
        
        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('Login')
                    ->assertPathIs('/findgame');
        });
    }
    public function testWrongEmailRedirect()
    {

        $user = factory(\App\User::class)->create();
        $wrong = 'wrong'.$user->email;
        
        $this->browse(function ($browser) use ($user, $wrong) {
            $browser->visit('/login')
                    ->type('email', $wrong)
                    ->type('password', 'secret')
                    ->press('Login')
                    ->assertPathIs('/login');
        });
    }
    public function testWrongPasswordRedirect()
    {

        $user = factory(\App\User::class)->create();
        
        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'wrong')
                    ->press('Login')
                    ->assertPathIs('/login');
        });
    }
}
