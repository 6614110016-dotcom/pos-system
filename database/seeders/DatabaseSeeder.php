<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * เรียกใช้: php artisan db:seed
 * ลำดับสำคัญ เพราะสินค้าอ้างอิงหมวดหมู่ และการขายอ้างอิงทั้งสินค้า สมาชิก พนักงาน
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            MemberSeeder::class,
            DemoSaleSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('=====================================================');
        $this->command->info('  ติดตั้งข้อมูลตัวอย่างเรียบร้อยแล้ว');
        $this->command->info('  บัญชีเข้าสู่ระบบ (รหัสผ่านทุกบัญชี: password)');
        $this->command->info('    ผู้ดูแลระบบ   : admin@pos.test');
        $this->command->info('    ผู้จัดการร้าน : manager@pos.test');
        $this->command->info('    พนักงานขาย   : cashier@pos.test');
        $this->command->info('=====================================================');
    }
}
