<?php

use App\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'username'          => 'superadmin',
            'password'          => Hash::make('mcfsystemsuper'),
            'role'              => 1
        ]);

        User::create([
            'name'              => 'IOT',
            'username'          => 'iot',
            'password'          => Hash::make('mcfsystemiot'),
            'role'              => 3
        ]);
    }
}
