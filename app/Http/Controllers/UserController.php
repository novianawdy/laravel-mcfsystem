<?php

namespace App\Http\Controllers;

use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request, User $user)
    {
        $user = $user->newQuery();

        if ($request->search) {
            /**  Check if search by is presence  */
            if ($request->search_by) {
                $user->where($request->search_by, 'like', '%' . $request->search . '%');
            } else {
                $user->where(function ($q) use ($request) {
                    $q->where('username', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%');
                });
            }
        }

        if ($request->start_date && $request->end_date) {
            $user->where('created_at', '>=', $request->start_date)
                ->where('created_at', '<=', $request->end_date);
        }

        if ($request->order_by) {
            $user->orderBy('created_at', $request->orderBy);
        } else {
            $user->orderBy('created_at', 'desc');
        }

        if ($request->pagination) {
            $pagination = $request->pagination;
        } else {
            $pagination = 10;
        }

        $result = $user->paginate($pagination);
        $result->withPath($request->getUri());

        return response()->json([
            'status'    => 'success',
            'result'    => $result
        ], 200);
    }

    public function update(Request $request)
    {
        $user = User::find($request->id);

        if ($user) {
            $request->validate([
                'username'  => 'required',
                'name'      => 'required',
            ]);

            DB::beginTransaction();

            $user->username = $request->username;
            $user->name = $request->name;
            $user->save();

            if (!$user) {
                DB::rollback();
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Something wrong when updating User.'
                ], 422);
            }

            DB::commit();

            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'user'  => $user
                ],

            ], 200);
        } else {
            return response()->json([
                'status'    => 'fail',
                'message'   => 'User not found.'
            ], 422);
        }
    }

    public function changePassword(Request $request)
    {
        $user = User::find($request->id);

        if ($user) {
            $request->validate([
                'user_password'     => 'required',
                'new_password'      => 'required',
                'confirm_password'  => 'required',
            ]);

            if (Auth::user()->role !== 1 && Auth::user()->id !== $request->id) {
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Access denied.'
                ], 403);
            }

            if (!Hash::check($request->user_password, Auth::user()->password)) {
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Password invalid.'
                ], 422);
            }

            if ($request->new_password !== $request->confirm_password) {
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Password not match.'
                ], 422);
            }

            DB::beginTransaction();

            $user->password = Hash::make($request->new_password);
            $user->save();

            if (!$user) {
                DB::rollback();
                return response()->json([
                    'status'    => 'fail',
                    'message'   => 'Something wrong when updating User password.'
                ], 422);
            }

            DB::commit();

            return response()->json([
                'status'    => 'success',
                'result'    => [
                    'user'  => $user
                ],

            ], 200);
        } else {
            return response()->json([
                'status'    => 'fail',
                'message'   => 'User not found.'
            ], 422);
        }
    }

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
