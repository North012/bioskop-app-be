<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'Kazuha',
            'email' => 'Kazuha@gmail.com',
            'password' => Hash::make('Kazuha12'),
            'phone_number' => '081257121757',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
