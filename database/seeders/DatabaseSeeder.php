<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Only seeds the admin user — safe to run anytime.
     */
    public function run(): void
    {
        if (!User::where('email', 'bscs2312405@szabist.pk')->exists()) {
            User::factory()->create([
                'name'     => 'ERP Admin',
                'email'    => 'bscs2312405@szabist.pk',
                'password' => bcrypt('abcd.1234'),
            ]);

            $this->command->info('✅ Admin user created: bscs2312405@szabist.pk');
        } else {
            $this->command->info('ℹ️  Admin user already exists — skipped.');
        }
    }
}

