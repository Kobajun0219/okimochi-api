<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOkimochisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('okimochis', function (Blueprint $table) {
            $table->id();
            $table->text('who');
            $table->string('title', 50);
            $table->text('message');
            $table->text('user_name');
            $table->integer('user_id')->unsigned();
            $table->string('pic_name')->nullable();
            $table->datetime('open_time');
            $table->text('open_place_name');
            $table->float('open_place_latitude', 12, 8);
            $table->float('open_place_longitude', 12, 8);
            $table->integer('public');
            $table->timestamps();

            //外部キー参照
            // $table->foreign('u_id')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('okimochis');
    }
}
