<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'notification_id', 'is_read'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function notification()
    {
        return $this->belongsTo('App\Notification', 'notification_id');
    }
}
