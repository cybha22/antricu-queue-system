<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Counter;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::create([
            'institution_name' => 'Rumah Sakit Permata Hati',
            'address' => 'Jl. AH. Nasution No. 34-36 Kel. Yosorejo Kec. Metro Timur Kota Metro -Lampung',
            'phone' => '08123456789',
        ]);

        $svcA = Service::create(['name' => 'Pemeriksaan Umum', 'prefix' => 'A', 'total_digits' => 3]);
        $svcB = Service::create(['name' => 'kesehatan ibu dan anak', 'prefix' => 'B', 'total_digits' => 3]);
        $svcC = Service::create(['name' => 'keluarga berencana (KB)', 'prefix' => 'C', 'total_digits' => 3]);
        $svcD = Service::create(['name' => 'Kesehatan Gigi dan Mulut', 'prefix' => 'D', 'total_digits' => 3]);

        $counter1 = Counter::create(['name' => 'Ruang 1', 'service_id' => $svcA->id]);
        $counter2 = Counter::create(['name' => 'Ruang 2', 'service_id' => $svcB->id]);
        $counter3 = Counter::create(['name' => 'Ruang 3', 'service_id' => $svcD->id]);
        $counter4 = Counter::create(['name' => 'Ruang 4', 'service_id' => $svcC->id]);

        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'counter_id' => null,
        ]);

        User::create([
            'name' => 'Petugas 1',
            'email' => 'petugas1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'counter_id' => $counter1->id,
        ]);

        User::create([
            'name' => 'Petugas 2',
            'email' => 'petugas2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'counter_id' => $counter2->id,
        ]);

        User::create([
            'name' => 'Petugas 3',
            'email' => 'petugas3@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'counter_id' => $counter3->id,
        ]);

        User::create([
            'name' => 'Petugas 4',
            'email' => 'petugas4@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'counter_id' => $counter4->id,
        ]);
    }
}
