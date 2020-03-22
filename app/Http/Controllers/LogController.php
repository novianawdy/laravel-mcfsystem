<?php

namespace App\Http\Controllers;

use App\Lib\NotificateUser;
use App\Log;
use App\Notification;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index(Request $request, Log $log)
    {
        $log = $log->newQuery();

        if ($request->search) {
            /**  Check if search by is presence  */
            if ($request->search_by) {
                $log->where($request->search_by, 'like', '%' . $request->search . '%');
            } else {
                $log->where(function ($q) use ($request) {
                    $q->where('flow', 'like', '%' . $request->search . '%')
                        ->orWhere('temperature', 'like', '%' . $request->search . '%');
                });
            }
        }

        if ($request->flow) {
            /**  Check if flow aggregate is presence  */
            if ($request->flow_is) {
                $log->where('flow', $request->flow_is, $request->flow);
            } else {
                $log->where('flow', 'like', '%' . $request->flow . '%');
            }
        }

        if ($request->temperature) {
            /**  Check if temperature aggregate is presence  */
            if ($request->temperature_is) {
                $log->where('temperature', $request->temperature_is, $request->temperature);
            } else {
                $log->where('temperature', 'like', '%' . $request->temperature . '%');
            }
        }

        if ($request->solenoid) {
            $log->where('solenoid', $request->solenoid);
        }

        if ($request->start_date && $request->end_date) {
            $log->where('created_at', '>=', $request->start_date)
                ->where('created_at', '<=', $request->end_date);
        }

        if ($request->order_by) {
            $log->orderBy('created_at', $request->orderBy);
        } else {
            $log->orderBy('created_at', 'desc');
        }

        if ($request->pagination) {
            $pagination = $request->pagination;
        } else {
            $pagination = 10;
        }

        $result = $log->paginate($pagination);
        $result->withPath($request->getUri());

        return response()->json([
            'status'    => 'success',
            'result'    => $result
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'flow'          => 'required',
            'temperature'   => 'required',
            'solenoid'      => 'required',
        ]);

        DB::beginTransaction();

        $notificate = Setting::where("key", "notificate")->first();
        $notificate_on = Setting::where("key", "notificate_on_temperature")->first();
        $last_log = Log::orderBy("created_at", "desc")->first();

        $log = Log::create([
            'flow'          => $request->flow,
            'temperature'   => $request->temperature,
            'solenoid'      => $request->solenoid,
        ]);

        if (!$log) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong when creating Log.'
            ], 422);
        }

        DB::commit();

        // send notification ketika temperature lebih besar dari nilai limit dan
        // temperature lebih besar dari temperature sebelumnya
        if (
            $notificate->value_decimal == 1 && // ketika setting notifikasi menyala
            $request->temperature >= $notificate_on->value_decimal && // ketika suhu lebih dari setting batas suhu
            $request->temperature > $last_log->temperature // ketika suhu terus naik
        ) {
            // creating notification
            $notification = Notification::create([
                'type'              => NotificateUser::TEMPERATURE,
                'title'             => 'temperatureReached',
                'body'              => 'temperatureReachedValue',
                'body_text'         => 'temperatureReachedValueText',
                'related_user_id'   => null
            ]);

            // notification payload
            // set null agar semua user di client mendapat notifikasi
            $payload['token'] = null;

            // broadcasting notification
            $broadcast_notification = new NotificateUser($notification);
            $broadcast_notification->payload($payload)
                ->toAll()
                ->send();
        }

        return response()->json([
            'status'    => 'success',
            'result'    => [
                'log'   => $log
            ],

        ], 200);
    }
}
