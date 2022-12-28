<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
                   'name' => 'admin',
                    'email' => 'admin@163.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make(123456), // password
                    'remember_token' => Str::random(10),
                    'api_token' => Str::random(80),
                ]);

        //$this->call(UserSeeder::class);
    }
}
