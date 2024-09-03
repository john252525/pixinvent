<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'sadmin@example.com',
            'password' => \Hash::make('12344321'),
        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => \Hash::make('12344321'),
        ]);
        User::factory()->create([
            'name' => 'Premium User',
            'email' => 'premium@example.com',
            'password' => \Hash::make('12344321'),
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => \Hash::make('12344321'),
        ]);

        User::factory(100)->create();

        $this->call(RoleAndPermissionsSeeder::class);
    }
}
