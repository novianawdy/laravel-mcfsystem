<?php

namespace App\Http\Controllers;

use App\Setting;
use Illuminate\Http\Request;

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

    public function update(Request $request, $key)
    {
        # code...
    }
}
