<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * โมดูลพนักงาน (Employee) - ข้อมูลพนักงานตัวอย่าง
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'employee_code' => 'EMP0001',
                'name'          => 'กันตธรณ์ แซ่หม่า',
                'email'         => 'admin@pos.test',
                'role'          => 'admin',
                'position'      => 'ผู้ดูแลระบบ',
                'phone'         => '081-000-0001',
                'salary'        => 30000,
            ],
            [
                'employee_code' => 'EMP0002',
                'name'          => 'สมชาย ใจดี',
                'email'         => 'manager@pos.test',
                'role'          => 'manager',
                'position'      => 'ผู้จัดการร้าน',
                'phone'         => '081-000-0002',
                'salary'        => 25000,
            ],
            [
                'employee_code' => 'EMP0003',
                'name'          => 'สมหญิง รักงาน',
                'email'         => 'cashier@pos.test',
                'role'          => 'cashier',
                'position'      => 'พนักงานแคชเชียร์',
                'phone'         => '081-000-0003',
                'salary'        => 15000,
            ],
            [
                'employee_code' => 'EMP0004',
                'name'          => 'ปรีชา ขยันขาย',
                'email'         => 'cashier2@pos.test',
                'role'          => 'cashier',
                'position'      => 'พนักงานแคชเชียร์',
                'phone'         => '081-000-0004',
                'salary'        => 15000,
            ],
        ];

        foreach ($employees as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => 'password',
                    'hired_at' => now()->subMonths(rand(3, 24))->toDateString(),
                    'status'   => true,
                ])
            );
        }
    }
}
