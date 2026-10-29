<?php

namespace Tests\Unit;

use App\Models\Member;
use App\Models\Product;
use App\Models\Setting;
use App\Services\PosCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ทดสอบการคำนวณเงินของตะกร้าสินค้า
 */
class PosCartTest extends TestCase
{
    use RefreshDatabase;

    protected PosCart $cart;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put('vat_rate', '7');
        Setting::put('point_rate', '100');

        $this->cart = new PosCart();
    }

    public function test_คำนวณยอดรวมถูกต้อง(): void
    {
        $product = Product::factory()->create(['price' => 25, 'stock_qty' => 100]);

        $this->cart->add($product, 4);        // 25 x 4 = 100

        $summary = $this->cart->summary();

        $this->assertEquals(100, $summary['subtotal']);
        $this->assertEquals(100, $summary['total']);
        $this->assertEquals(6.54, $summary['vat']);   // VAT รวมในราคา 7%
    }

    public function test_ส่วนลดสมาชิกระดับทองคิด3เปอร์เซ็นต์(): void
    {
        $product = Product::factory()->create(['price' => 100, 'stock_qty' => 100]);
        $member  = Member::factory()->create(['tier' => 'gold']);

        $this->cart->add($product, 10);       // 1,000 บาท
        $this->cart->setMember($member->id);

        $summary = $this->cart->summary();

        $this->assertEquals(30, $summary['member_discount']);
        $this->assertEquals(970, $summary['total']);
        $this->assertSame(9, $summary['points_earned']);
    }

    public function test_ส่วนลดท้ายบิลถูกหักออกจากยอดสุทธิ(): void
    {
        $product = Product::factory()->create(['price' => 50, 'stock_qty' => 100]);

        $this->cart->add($product, 4);        // 200 บาท
        $this->cart->setBillDiscount(50);

        $this->assertEquals(150, $this->cart->summary()['total']);
    }

    public function test_ล้างตะกร้าแล้วว่างเปล่า(): void
    {
        $product = Product::factory()->create(['price' => 10, 'stock_qty' => 100]);

        $this->cart->add($product, 1);
        $this->cart->clear();

        $this->assertTrue($this->cart->isEmpty());
    }
}
