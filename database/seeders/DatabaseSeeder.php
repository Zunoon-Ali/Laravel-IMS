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
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (!User::where('email', 'bscs2312405@szabist.pk')->exists()) {
            User::factory()->create([
                'name' => 'ERP Admin',
                'email' => 'bscs2312405@szabist.pk',
                'password' => bcrypt('abcd.1234'),
            ]);
        }

        $this->call(PersonalModuleSeeder::class);
    }
}
