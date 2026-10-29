<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * โมดูลสินค้า (Stock) - หมวดหมู่สินค้าตัวอย่าง
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT001', 'name' => 'เครื่องดื่ม',        'description' => 'น้ำอัดลม น้ำเปล่า ชา กาแฟ นม'],
            ['code' => 'CAT002', 'name' => 'ขนมขบเคี้ยว',       'description' => 'มันฝรั่งทอด ขนมปัง ลูกอม ช็อกโกแลต'],
            ['code' => 'CAT003', 'name' => 'อาหารสำเร็จรูป',    'description' => 'บะหมี่กึ่งสำเร็จรูป อาหารกระป๋อง'],
            ['code' => 'CAT004', 'name' => 'ของใช้ในบ้าน',      'description' => 'สบู่ ยาสระผม ผงซักฟอก น้ำยาล้างจาน'],
            ['code' => 'CAT005', 'name' => 'เครื่องเขียน',       'description' => 'ปากกา ดินสอ สมุด ยางลบ'],
            ['code' => 'CAT006', 'name' => 'สินค้าเบ็ดเตล็ด',    'description' => 'ถ่านไฟฉาย ไฟแช็ก สินค้าทั่วไป'],
        ];

        foreach ($categories as $c) {
            Category::updateOrCreate(['code' => $c['code']], array_merge($c, ['status' => true]));
        }
    }
}
