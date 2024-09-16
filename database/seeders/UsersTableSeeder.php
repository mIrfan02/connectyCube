<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Alice',
                'email' => 'alice@example.com',
                'password' => 'password123',
            ],
            [
                'name' => 'Bob',
                'email' => 'bob@example.com',
                'password' => 'password123',
            ],
            // Add more users as needed
        ];

        foreach ($users as $userData) {
            // Create Laravel user
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                // Optionally, you can store ConnectyCube IDs later if needed
            ]);
        }
    }
}
