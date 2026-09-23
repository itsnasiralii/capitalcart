<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Primary admin (CapitalCart)
        User::updateOrCreate(
            ['email' => 'nasirali@capitalcart.pk'],
            [
                'name'               => 'Nasir Ali',
                'password'           => Hash::make('nasirali123'),
                'is_admin'           => true,
                'email_verified_at'  => now(),
            ]
        );

        // Alias admin
        User::updateOrCreate(
            ['email' => 'nasirali@marketory.com'],
            [
                'name'               => 'Nasir Ali',
                'password'           => Hash::make('nasirali123'),
                'is_admin'           => true,
                'email_verified_at'  => now(),
            ]
        );
    }
}
