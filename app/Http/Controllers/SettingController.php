<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;
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

            foreach ($settings as $value) {
                $result[$value->type][] = $value;
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

        foreach ($request->settings as $requested) {
            $setting = Setting::where('key', $requested["key"])->first();
            if (!$setting) {
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Setting key not found.'
                ], 422);
            }

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

        return response()->json([
            'status'    => 'success',
            'result'    => 'Success',

        ], 200);
    }
}
