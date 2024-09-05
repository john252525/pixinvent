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
            'name' => 'Domain1 User',
            'email' => 'domain1@example.com',
            'password' => \Hash::make('12344321'),
        ]);

        User::factory()->create([
            'name' => 'Domain2 User',
            'email' => 'domain2@example.com',
            'password' => \Hash::make('12344321'),
        ]);

        User::factory()->create([
            'name' => 'Domain3 User',
            'email' => 'domain3@example.com',
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
