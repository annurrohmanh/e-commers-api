<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Administrator',
                'username' => 'admin',
                'email'    => 'admin@example.com',
                'phone'    => '081234567890',
                'address'  => 'Jl. Admin No. 1',
                'password' => 'Admin@1234',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Default Seller',
                'username' => 'seller1',
                'email'    => 'seller@example.com',
                'phone'    => '081234567891',
                'address'  => 'Jl. Seller No. 1',
                'password' => 'Seller@123',
                'role'     => 'seller',
            ],
            [
                'name'     => 'Default Buyer',
                'username' => 'buyer1',
                'email'    => 'buyer@example.com',
                'phone'    => '081234567892',
                'address'  => 'Jl. Buyer No. 1',
                'password' => 'Buyer@1234',
                'role'     => 'buyer',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrCreate(
                [
                    'email'    => $data['email'],
                    'username' => $data['username']
                ],
                [
                    'name'                 => $data['name'],
                    'username'             => $data['username'],
                    'phone'                => $data['phone'],
                    'address'              => $data['address'],
                    'password'             => Hash::make($data['password']),
                    'email_verified_at'    => Carbon::now(),
                    'profile_completed_at' => Carbon::now(),
                ]
            );

            $user->syncRoles([$data['role']]);
        }
    }
}