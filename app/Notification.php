<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'channel', 'type', 'title', 'body', 'body_text', 'related_user_id'
    ];

    public function related_user()
    {
        return $this->belongsTo('App\User', 'related_user_id');
    }

    public function notification_users()
    {
        return $this->hasMany('App\NotificationUser', 'notification_id', 'id');
    }

    public function notification_user()
    {
        return $this->hasOne('App\NotificationUser', 'notification_id', 'id');
    }

    // get notification for broadcasted user
    public function getCurrentNotification($user_id)
    {
        return $this->with(['notification_user' => function ($notification_user) use ($user_id) {
            $notification_user->where('user_id', $user_id);
        }])
            ->with('related_user');
    }
}
