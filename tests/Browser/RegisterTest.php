<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Faker;

class RegisterTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testRegisterFormFilling()
    {
        $faker = Faker\Factory::create();
        $password = $faker->password;

        $this->browse(function ($browser) use ($faker, $password) {
            $browser->visit('/register')
                    ->type('name', $faker->firstName)
                    ->type('last_name', $faker->lastName)
                    ->type('email', $faker->email)
                    ->type('password', $password)
                    ->type('password_confirmation', $password)
                    ->press('Register')
                    ->assertPathIs('/findgame');
        });
    }
}
