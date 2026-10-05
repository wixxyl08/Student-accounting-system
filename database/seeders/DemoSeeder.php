<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Education;
use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('login', 'admin')->first();
        $adminId = $admin?->id;

        // 1. Организации
        $org1 = Organization::create([
            'full_name' => 'ООО «Ромашка»',
            'short_name' => 'Ромашка',
            'inn' => '7701234567',
            'kpp' => '770101001',
            'ogrn' => '1027700123456',
            'legal_address' => 'г. Москва, ул. Ленина, д. 1',
            'actual_address' => 'г. Москва, ул. Ленина, д. 1',
            'phone' => '+7 (495) 123-45-67',
            'email' => 'info@romashka.ru',
            'contact_person' => 'Иванов Иван Иванович',
            'contact_position' => 'Генеральный директор',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $org2 = Organization::create([
            'full_name' => 'ООО «Василёк»',
            'short_name' => 'Василёк',
            'inn' => '7709876543',
            'kpp' => '770901001',
            'ogrn' => '1027700987654',
            'legal_address' => 'г. Москва, ул. Пушкина, д. 10',
            'phone' => '+7 (495) 987-65-43',
            'email' => 'info@vasilek.ru',
            'contact_person' => 'Петрова Мария Сергеевна',
            'contact_position' => 'Директор',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        // 2. Сотрудники
        $emp1 = Employee::create([
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
            'birth_date' => '1985-06-15',
            'phone' => '+7 (999) 123-45-67',
            'email' => 'ivanov@romashka.ru',
            'organization_id' => $org1->id,
            'position' => 'Генеральный директор',
            'status' => 'active',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $emp2 = Employee::create([
            'last_name' => 'Петрова',
            'first_name' => 'Мария',
            'middle_name' => 'Сергеевна',
            'birth_date' => '1990-03-20',
            'phone' => '+7 (999) 765-43-21',
            'email' => 'petrova@vasilek.ru',
            'organization_id' => $org2->id,
            'position' => 'Бухгалтер',
            'status' => 'active',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $emp3 = Employee::create([
            'last_name' => 'Сидоров',
            'first_name' => 'Пётр',
            'middle_name' => 'Алексеевич',
            'phone' => '+7 (999) 555-55-55',
            'organization_id' => null,
            'position' => 'Фрилансер',
            'status' => 'active',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        // 3. Образование
        Education::create([
            'employee_id' => $emp1->id,
            'level' => 'higher',
            'institution' => 'МГУ им. М.В. Ломоносова',
            'graduation_year' => 2008,
            'specialty' => 'Юриспруденция',
        ]);

        Education::create([
            'employee_id' => $emp2->id,
            'level' => 'higher',
            'institution' => 'Финансовый университет',
            'graduation_year' => 2013,
            'specialty' => 'Бухгалтерский учёт',
        ]);

        // 4. Программы
        $prog1 = Program::create([
            'name' => 'Охрана труда для руководителей',
            'description' => 'Обучение по охране труда',
            'price' => 3500,
            'education_requirement' => 'higher',
            'retraining_period' => '1_year',
            'duration' => '40 часов',
            'status' => 'active',
            'notify_days_before' => 60,
        ]);

        $prog2 = Program::create([
            'name' => 'Пожарная безопасность',
            'description' => 'Обучение по пожарной безопасности',
            'price' => 2500,
            'education_requirement' => 'none',
            'retraining_period' => '3_years',
            'duration' => '24 часа',
            'status' => 'active',
            'notify_days_before' => 60,
        ]);

        // 5. Группы
        $group1 = Group::create([
            'name' => 'Охрана труда — Группа 1 — 2026',
            'program_id' => $prog1->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'status' => 'ongoing',
            'note' => 'Первая группа',
        ]);

        $group2 = Group::create([
            'name' => 'Пожарная безопасность — Группа 1 — 2026',
            'program_id' => $prog2->id,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-15',
            'status' => 'recruiting',
        ]);

        // 6. Зачисления
        Enrollment::create([
            'employee_id' => $emp1->id,
            'group_id' => $group1->id,
            'status' => 'completed',
            'completed_at' => now(),
            'next_training_date' => now()->addYear(),
            'certificate_number' => 'УД-2026-0001',
        ]);

        Enrollment::create([
            'employee_id' => $emp2->id,
            'group_id' => $group2->id,
            'status' => 'enrolled',
        ]);

        Enrollment::create([
            'employee_id' => $emp3->id,
            'group_id' => $group1->id,
            'status' => 'studying',
        ]);

        // 7. Договор
        Contract::create([
            'number' => 'Д-2026-0001',
            'group_id' => $group1->id,
            'organization_id' => $org1->id,
            'program_id' => $prog1->id,
            'total_amount' => 3500,
        ]);
    }
}