<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;

/**
 * โมดูลสมาชิก (Member) - สมาชิกตัวอย่าง
 */
class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            ['name' => 'อนุชา พงศ์ไพบูลย์',  'phone' => '0810000101', 'gender' => 'male',   'tier' => 'platinum'],
            ['name' => 'ศิริพร แก้วมณี',     'phone' => '0810000102', 'gender' => 'female', 'tier' => 'gold'],
            ['name' => 'ธนากร วงศ์สุวรรณ',   'phone' => '0810000103', 'gender' => 'male',   'tier' => 'gold'],
            ['name' => 'พิมพ์ชนก ใจงาม',    'phone' => '0810000104', 'gender' => 'female', 'tier' => 'silver'],
            ['name' => 'วีระชัย ทองดี',      'phone' => '0810000105', 'gender' => 'male',   'tier' => 'silver'],
            ['name' => 'นภัสสร บุญมี',       'phone' => '0810000106', 'gender' => 'female', 'tier' => 'silver'],
            ['name' => 'ชัยวัฒน์ ศรีสมบูรณ์', 'phone' => '0810000107', 'gender' => 'male',   'tier' => 'silver'],
            ['name' => 'กมลชนก รุ่งเรือง',   'phone' => '0810000108', 'gender' => 'female', 'tier' => 'gold'],
        ];

        foreach ($members as $i => $m) {
            Member::updateOrCreate(
                ['phone' => $m['phone']],
                array_merge($m, [
                    'member_code' => 'MB'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'email'       => 'member'.($i + 1).'@example.com',
                    'address'     => 'จ.ขอนแก่น',
                    'birth_date'  => now()->subYears(rand(18, 55))->subDays(rand(0, 364))->toDateString(),
                    'points'      => 0,
                    'total_spent' => 0,
                    'joined_at'   => now()->subDays(rand(30, 400))->toDateString(),
                    'status'      => true,
                ])
            );
        }
    }
}
