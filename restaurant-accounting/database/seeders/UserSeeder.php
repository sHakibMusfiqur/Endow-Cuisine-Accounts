<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'John Accountant',
                'email' => 'accountant@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'accountant',
            ],
            [
                'name' => 'Jane Manager',
                'email' => 'manager@restaurant.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);
            $user->assignRole($role);
        }
    }
}
