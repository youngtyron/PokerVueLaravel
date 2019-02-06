<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bank');
            $table->string('first_suit')->nullable();
            $table->integer('first_rank')->nullable();
            $table->string('second_suit')->nullable();
            $table->integer('second_rank')->nullable();
            $table->string('third_suit')->nullable();
            $table->integer('third_rank')->nullable();
            $table->string('fourth_suit')->nullable();
            $table->integer('fourth_rank')->nullable();
            $table->string('fifth_suit')->nullable();
            $table->integer('fifth_rank')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rounds');
    }
}
