<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class InitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkUserTable = User::count();
        if ($checkUserTable == 0) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'sa@app.com',
                'password' => 'password',
                'status' => StatusEnum::ACTIVE,
            ]);
        }
    }
}
