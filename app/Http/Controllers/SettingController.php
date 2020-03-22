<?php

namespace App\Http\Controllers;

use App\Lib\NotificateUser;
use App\Notification;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index(Request $request, Setting $setting)
    {
        $setting = $setting->newQuery();

        if ($request->key) {
            $setting->where('key', $request->key);
            $result = $setting->first();

            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'setting'   => $result
                ]
            ], 200);
        }

        if ($request->grouped) {
            $result = [];
            $settings = $setting->get();

            foreach ($settings as $values) {
                $result[$values->type][] = $values;
            }

            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'setting'   => $result
                ]
            ], 200);
        }

        if ($request->serialized) {
            $result = [];
            $settings = $setting->get();

            foreach ($settings as $values) {
                $value = null;
                if ($values->value) $value = $values->value;
                else if ($values->value_text) $value = $values->value_text;
                else if ($values->value_decimal) $value = $values->value_decimal;

                $result[$values->key] = $value;
            }

            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'setting'   => $result
                ]
            ], 200);
        }

        $result = $setting->get();

        return response()->json([
            'status'    => 'success',
            'result'    => [
                'setting'   => $result
            ]
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            'key'   => 'required',
        ]);

        $setting = Setting::where('key', $request->key)->first();
        if (!$setting) {
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Setting key not found.'
            ], 422);
        }

        $setting_type = $setting->type;

        DB::beginTransaction();

        $setting->value = $request->value;
        $setting->value_text = $request->value_text;
        $setting->value_decimal = $request->value_decimal;
        $setting->save();

        if (!$setting) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong when updating Setting.'
            ], 422);
        }

        DB::commit();

        // creating notification
        $notification = null;
        if ($setting_type === "global_setting") {
            $notification = Notification::create([
                'type'              => NotificateUser::SETTING_CHANGE,
                'title'             => 'settingChanged',
                'body'              => 'settingChangedBy',
                'body_text'         => 'settingChangedByText',
                'related_user_id'   => Auth::id()
            ]);
        }
        if ($setting_type === "mock_setting") {
            $notification = Notification::create([
                'type'              => NotificateUser::TEMPERATURE,
                'title'             => 'temperatureChanged',
                'body'              => 'temperatureChangedBy',
                'body_text'         => 'temperatureChangedByText',
                'related_user_id'   => Auth::id()
            ]);
        }

        // notification payload
        $grouped = [];
        $serialized = [];
        $settings = Setting::get();

        foreach ($settings as $values) {
            $grouped[$values->type][] = $values;

            $value = null;
            if ($values->value) $value = $values->value;
            else if ($values->value_text) $value = $values->value_text;
            else if ($values->value_decimal) $value = $values->value_decimal;

            $serialized[$values->key] = $value;
        }

        $payload['token'] = $request->header('Authorization');
        $payload['setting'] = $grouped;
        $payload['setting_serialized'] = $serialized;

        // broadcasting notification
        $broadcast_notification = new NotificateUser($notification);
        $broadcast_notification->payload($payload)
            ->toAll()
            ->send();

        return response()->json([
            'status'    => 'success',
            'result'    => [
                'setting'   => $setting
            ],

        ], 200);
    }

    public function bulkUpdate(Request $request)
    {
        DB::beginTransaction();

        $setting_type = null;

        foreach ($request->settings as $requested) {
            $setting = Setting::where('key', $requested["key"])->first();
            if (!$setting) {
                DB::rollback();
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Setting key not found.'
                ], 422);
            }

            if ($setting->type != $setting_type && $setting_type != null) {
                DB::rollback();
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Setting not on same type.'
                ], 422);
            }

            $setting_type = $setting->type;

            $setting->value = $requested["value"];
            $setting->value_text = $requested["value_text"];
            $setting->value_decimal = $requested["value_decimal"];
            $setting->save();

            if (!$setting) {
                DB::rollback();
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Something wrong when updating Setting.'
                ], 422);
            }
        }

        DB::commit();

        // creating notificatoin
        $notification = null;
        if ($setting_type === "global_setting") {
            $notification = Notification::create([
                'type'              => NotificateUser::SETTING_CHANGE,
                'title'             => 'settingChanged',
                'body'              => 'settingChangedBy',
                'body_text'         => 'settingChangedByText',
                'related_user_id'   => Auth::id()
            ]);
        }
        if ($setting_type === "mock_setting") {
            $notification = Notification::create([
                'type'              => NotificateUser::TEMPERATURE,
                'title'             => 'temperatureChanged',
                'body'              => 'temperatureChangedBy',
                'body_text'         => 'temperatureChangedByText',
                'related_user_id'   => Auth::id()
            ]);
        }

        // notification payload
        $grouped = [];
        $serialized = [];
        $settings = Setting::get();

        foreach ($settings as $values) {
            $grouped[$values->type][] = $values;

            $value = null;
            if ($values->value) $value = $values->value;
            else if ($values->value_text) $value = $values->value_text;
            else if ($values->value_decimal) $value = $values->value_decimal;

            $serialized[$values->key] = $value;
        }

        $payload['token'] = $request->header('Authorization');
        $payload['setting'] = $grouped;
        $payload['setting_serialized'] = $serialized;

        // broadcasting notification
        $broadcast_notification = new NotificateUser($notification);
        $broadcast_notification->payload($payload)
            ->toAll()
            ->send();

        return response()->json([
            'status'    => 'success',
            'result'    => 'Success',

        ], 200);
    }
}
