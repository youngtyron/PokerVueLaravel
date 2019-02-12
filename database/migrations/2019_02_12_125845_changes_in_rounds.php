<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInRounds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     Schema::table('rounds', function (Blueprint $table) {
        $table->dropColumn('first_suit');
        $table->dropColumn('first_rank');
        $table->dropColumn('second_suit');
        $table->dropColumn('second_rank');
        $table->dropColumn('third_suit');
        $table->dropColumn('third_rank');
        $table->dropColumn('fourth_suit');
        $table->dropColumn('fourth_rank');
        $table->dropColumn('fifth_suit');
        $table->dropColumn('fifth_rank');

        $table->string('first_card')->nullable();
        $table->string('second_card')->nullable();
        $table->string('third_card')->nullable();
        $table->string('fourth_card')->nullable();
        $table->string('fifth_card')->nullable();

        $table->integer('betted')->default(0);
        });
    }
    public function down()
    {
        //
    }
}
