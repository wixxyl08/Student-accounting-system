<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Администратор — полный доступ
        User::create([
            'full_name' => 'Администратор Системы',
            'login' => 'admin',
            'password' => 'admin123',  // хешируется автоматически (см. casts)
            'role' => 'admin',
            'is_blocked' => false,
        ]);

        // Методист — ограниченный доступ
        User::create([
            'full_name' => 'Иванова Ирина Ивановна',
            'login' => 'methodist',
            'password' => 'methodist123',
            'role' => 'methodist',
            'is_blocked' => false,
        ]);

        $this->command->info('✅ Создан администратор: admin / admin123');
        $this->command->info('✅ Создан методист: methodist / methodist123');
    }
}