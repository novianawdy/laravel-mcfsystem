<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notification;
use App\NotificationUser;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request, Notification $notifications)
    {
        $notifications = $notifications->newQuery();
        $pagination = 5;

        $notifications->with(['notification_user' => function ($notification_user) {
            $notification_user->where('user_id', Auth::user()->id);
        }]);
        $notifications->with('related_user');

        if ($request->show_unread_only) {
            $notifications->whereHas('notification_users', function ($notification_users) {
                $notification_users->where('user_id', Auth::user()->id)
                    ->where('is_read', 0);
            });
        } else {
            $notifications->whereHas('notification_users', function ($notification_users) {
                $notification_users->where('user_id', Auth::user()->id);
            });
        }

        if ($request->pagination) {
            $pagination = $request->pagination;
        }

        $result = $notifications->orderBy('created_at', 'desc')
            ->paginate($pagination);
        $result->withPath($request->getUri());
        $info = collect([
            "total_unview" => NotificationUser::where('user_id', Auth::user()->id)
                ->where('is_read', 0)
                ->count()
        ]);
        $result = $info->merge($result);

        return response()->json([
            'status'    => 'success',
            'result'    => $result
        ], 200);
    }

    public function markAllAsRead()
    {
        DB::beginTransaction();

        $result = NotificationUser::where('user_id', Auth::user()->id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        if (!$result && $result != 0) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong when marking notification as read.'
            ], 422);
        }

        DB::commit();
        return response()->json([
            'status'    => 'success',
            'result'    => $result
        ], 200);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required',
        ]);

        DB::beginTransaction();

        $result = NotificationUser::where('notification_id', $request->notification_id)
            ->where('user_id', Auth::user()->id)
            ->update(['is_read' => 1]);
        if (!$result) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong when marking notification as read.'
            ], 422);
        }

        DB::commit();
        return response()->json([
            'status'    => 'success',
            'result'    => $result
        ], 200);
    }
}
