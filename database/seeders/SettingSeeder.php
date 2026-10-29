<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * โมดูลอื่นๆ - ค่าตั้งต้นของระบบ
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'shop_name',    'value' => 'ร้านสะดวกซื้อ KAN MART',                        'label' => 'ชื่อร้าน'],
            ['key' => 'shop_address', 'value' => '123 ถ.มหาวิทยาลัย ต.ในเมือง อ.เมือง จ.ขอนแก่น 40000', 'label' => 'ที่อยู่ร้าน'],
            ['key' => 'shop_phone',   'value' => '043-000-000',                                   'label' => 'เบอร์โทรศัพท์'],
            ['key' => 'shop_tax_id',  'value' => '0405561000001',                                 'label' => 'เลขประจำตัวผู้เสียภาษี'],
            ['key' => 'vat_rate',     'value' => '7',                                             'label' => 'อัตราภาษีมูลค่าเพิ่ม (%)'],
            ['key' => 'point_rate',   'value' => '100',                                           'label' => 'ซื้อครบกี่บาทได้ 1 แต้ม'],
            ['key' => 'receipt_note', 'value' => 'ขอบคุณที่ใช้บริการ แล้วพบกันใหม่',                  'label' => 'ข้อความท้ายใบเสร็จ'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'group' => 'general', 'label' => $s['label']]
            );
        }
    }
}
