<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('channel')->nullable();
            $table->tinyInteger('type')->nullable()->comment('1=temperature, 2=profile_change, 3=password_change, 4=setting_change, 5=hide_popup');
            $table->string('title');
            $table->string('body');
            $table->string('body_text')->nullable();
            $table->bigInteger('related_user_id')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
