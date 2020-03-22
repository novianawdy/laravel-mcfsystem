<?php

namespace App\Lib;

use App\User;
use App\Notification;

use Illuminate\Support\Facades\Auth;

use App\Events\NotificationEvent;

class NotificateUser
{
    public const TEMPERATURE = 1;
    public const PROFILE_CHANGE = 2;
    public const PASSWORD_CHANGE = 3;
    public const SETTING_CHANGE = 4;

    protected $notification;
    protected $payload;
    protected $to_all = false;
    protected $to_others = false;
    protected $user;
    protected $users;

    /**
     * Create a new NotificateUser instance.
     *
     * @param object $notification Eloquent Notifikasi Instance
     * @param object $payload Informasi tambahan yang akan diberikan
     * @return void
     */
    public function __construct(Notification $notification = null, $payload = null)
    {
        $this->notification = $notification;
        $this->payload = $payload;
    }

    public function send()
    {
        $sent = false;

        if ($this->user) {
            $sent = $this->formatNotification($this->user);
        } else if ($this->users) {
            foreach ($this->users as $user) {
                $sent = $this->formatNotification($user);
            }
        } else if ($this->to_others) {
            $users = User::where('id_user', '!=', Auth::user()->id_user)->get();
            foreach ($users as $user) {
                $sent = $this->formatNotification($user);
            }
        } else if ($this->to_all) {
            $users = User::get();
            foreach ($users as $user) {
                $sent = $this->formatNotification($user);
            }
        }

        return $sent;
    }

    private function formatNotification($user)
    {
        $user_id = $user->id;

        $this->notification->notification_users()->create([
            'user_id' => $user_id
        ]);

        // formatting notification for each user
        event(new NotificationEvent($this->notification->load(["notification_user", "related_user"]), $this->payload, 'notification.' . $user_id));

        return true;
    }

    /**
     * @param Notification $notification Eloquent Notifikasi Instance
     */
    public function notification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param object $payload Informasi tambahan yang akan diberikan
     */
    public function payload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * Kirim notifikasi ke semua user
     *
     * @param boolean $to_all
     */
    public function toAll($to_all = true)
    {
        $this->to_all = $to_all;
        return $this;
    }

    /**
     * Kirim notifikasi ke semua user kecuali user terkait
     *
     * @param boolean $to_others
     */
    public function toOthers($to_others = true)
    {
        $this->to_others = $to_others;
        return $this;
    }

    /**
     * Kirim notifikasi ke satu user
     *
     * @param User $user User Object Instance
     */
    public function toUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Kirim notifikasi ke beberapa user
     *
     * @param User $users Array of User Object Instance
     */
    public function toUsers(User $users)
    {
        $this->users = $users;
        return $this;
    }
}
