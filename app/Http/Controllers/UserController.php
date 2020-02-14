<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                'status' => 'success',
                'token'    => $token
            ], $this->successStatus);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Email or password combination invalid.'], 401);
        }
    }

    public function show()
    {
        $user = User::find(Auth::user()->id);

        return response()->json([
            'status'    => 'succes',
            'result'    => $user
        ], 200);
    }
}
