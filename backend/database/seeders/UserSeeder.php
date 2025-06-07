<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@maurtiustelecom.mu',
            'password' => bcrypt('password'),
            'entra_id' => null,
        ]);

        \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@maurtiustelecom.mu',
            'password' => bcrypt('password'),
            'entra_id' => 'test-entra-id-123',
        ]);

        \App\Models\User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@maurtiustelecom.mu',
            'password' => bcrypt('password'),
            'entra_id' => null,
        ]);
    }
}
