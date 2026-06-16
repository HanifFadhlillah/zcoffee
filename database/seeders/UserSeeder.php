<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@zcoffee.id'],
            [
                'name'     => 'Admin ZCoffee',
                'email'    => 'admin@zcoffee.id',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@zcoffee.id'],
            [
                'name'     => 'Kasir Utama',
                'email'    => 'kasir@zcoffee.id',
                'password' => Hash::make('password'),
                'role'     => 'cashier',
            ]
        );

        $this->command->info('✅ Users seeded: admin@zcoffee.id & kasir@zcoffee.id (password: password)');
    }
}
