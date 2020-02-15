<?php

use App\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** Flag On/Off solenoid */
        Setting::create([
            'key'               => 'solenoid',
            'value_decimal'     => 0,
            'type'              => 'global_setting'
        ]);

        /** Flag On/Off notification feature */
        Setting::create([
            'key'               => 'notificate',
            'value_decimal'     => 0,
            'type'              => 'global_setting'
        ]);

        /** Notificate user when temperature reach value */
        Setting::create([
            'key'               => 'notificate_on_temperature',
            'value_decimal'     => 0,
            'type'              => 'global_setting'
        ]);

        /** Flag On/Off mock temperature */
        Setting::create([
            'key'               => 'mock_temperature',
            'value_decimal'     => 0,
            'type'              => 'mock_setting'
        ]);

        /** Fake temperature value */
        Setting::create([
            'key'               => 'fake_temperature',
            'value_decimal'     => 0,
            'type'              => 'mock_setting'
        ]);
    }
}
