<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNotificationUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_users', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_notification_users_users')
                ->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('notification_id', 'fk_notification_users_notifications')
                ->references('id')->on('notifications')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_users', function (Blueprint $table) {
            $table->dropForeign('fk_notification_users_users');
            $table->dropForeign('fk_notification_users_notifications');
        });
    }
}
