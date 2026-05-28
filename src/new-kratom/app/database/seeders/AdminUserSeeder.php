<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::firstOrCreate(
            ['email' => 'admin@vivadzen.cz'],
            [
                'name' => 'Vivadzen Admin',
                'password' => 'admin12345', // hashed автоматически кастом 'hashed'
                'role' => 'admin',
            ]
        );
    }
}
