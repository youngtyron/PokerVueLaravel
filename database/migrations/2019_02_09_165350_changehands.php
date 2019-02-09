<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Changehands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('hands', function (Blueprint $table) {
        $table->dropColumn('first_suit');
        $table->dropColumn('first_rank');
        $table->dropColumn('second_suit');
        $table->dropColumn('second_rank');
        $table->string('first_card');
        $table->string('second_card');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
