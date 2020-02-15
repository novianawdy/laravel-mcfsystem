<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required',
            'password'  => 'required'
        ]);

        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $token =  $user->createToken('MCFSystem')->accessToken;
            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'token' => $token,
                    'user'  => $user
                ],

            ], 200);
        } else {
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Email or password combination invalid.'
            ], 402);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'email'     => 'required',
            'password'  => 'required',
            'name'      => 'required',

        ]);

        DB::beginTransaction();

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'email_verified_at' => Carbon::now(),
            'password'          => Hash::make($request->password),
            'role'              => 2
        ]);

        if (!$user) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong on creating User.'
            ], 402);
        }

        DB::commit();

        return response()->json([
            'status'    => 'success',
            'result'    => [
                'user'  => $user
            ],

        ], 200);
    }

    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'status'    => 'success',
            'result'    => $user
        ], 200);
    }
}
