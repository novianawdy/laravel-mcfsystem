<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@mcfsystem.com',
            'email_verified_at' => Carbon::now(),
            'password'          => Hash::make('mcfsystemsuper'),
            'role'              => 1
            // 'api_token'=>Hash::make(Str::random(80))
        ]);
    }
}
