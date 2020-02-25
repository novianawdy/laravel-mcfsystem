<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username'  => 'required',
            'password'  => 'required'
        ]);

        if (Auth::attempt(['username' => request('username'), 'password' => request('password')])) {
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
                'message'   => 'Username or password combination invalid.'
            ], 422);
        }
    }

    public function logout()
    {
        $user = Auth::user()->token();
        $user->revoke();

        return response()->json([
            'status'    => 'success',
            'result'    => "Successfully logout",

        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'username'  => 'required',
            'password'  => 'required',
            'name'      => 'required',

        ]);

        DB::beginTransaction();

        $user = User::create([
            'name'              => $request->name,
            'username'          => $request->username,
            'password'          => Hash::make($request->password),
            'role'              => 2
        ]);

        if (!$user) {
            DB::rollback();
            return response()->json([
                'status'    => 'fail',
                'message'   => 'Something wrong when creating User.'
            ], 422);
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
