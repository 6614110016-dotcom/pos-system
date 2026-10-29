<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ทดสอบโมดูลการขาย (Sale) และโมดูลจ่ายเงิน (Payment)
 */
class SaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put('vat_rate', '7');
        Setting::put('point_rate', '100');

        $this->cashier = User::factory()->create();
        $this->actingAs($this->cashier);
    }

    public function test_เพิ่มสินค้าลงตะกร้าได้(): void
    {
        $product = Product::factory()->create(['stock_qty' => 10, 'price' => 20]);

        $this->post(route('pos.cart.add'), ['product_id' => $product->id, 'qty' => 2])
             ->assertRedirect();

        $this->assertSame(2, session('pos.items')[$product->id]['qty']);
    }

    public function test_เพิ่มสินค้าเกินจำนวนคงเหลือไม่ได้(): void
    {
        $product = Product::factory()->create(['stock_qty' => 1, 'price' => 20]);

        $this->post(route('pos.cart.add'), ['product_id' => $product->id, 'qty' => 5])
             ->assertSessionHas('error');

        $this->assertEmpty(session('pos.items', []));
    }

    public function test_เปิดบิลแล้วสต็อกถูกตัดถูกต้อง(): void
    {
        $product = Product::factory()->create(['stock_qty' => 10, 'price' => 100]);

        $this->post(route('pos.cart.add'), ['product_id' => $product->id, 'qty' => 3]);
        $this->post(route('pos.checkout'));

        $sale = Sale::first();

        $this->assertNotNull($sale);
        $this->assertSame('pending', $sale->status);
        $this->assertEquals(300, (float) $sale->total);
        $this->assertSame(7, $product->fresh()->stock_qty);
    }

    public function test_รับชำระเงินครบแล้วบิลเปลี่ยนเป็นชำระแล้ว(): void
    {
        $product = Product::factory()->create(['stock_qty' => 10, 'price' => 100]);

        $this->post(route('pos.cart.add'), ['product_id' => $product->id, 'qty' => 1]);
        $this->post(route('pos.checkout'));

        $sale = Sale::first();

        $this->post(route('payments.store', $sale), [
            'method'   => 'cash',
            'amount'   => 100,
            'received' => 200,
        ])->assertRedirect(route('sales.receipt', $sale));

        $sale->refresh();

        $this->assertSame('paid', $sale->status);
        $this->assertEquals(100, $sale->paid_amount);
        $this->assertEquals(100, (float) $sale->payments()->first()->change_amount);
    }

    public function test_ยกเลิกบิลแล้วสต็อกถูกคืน(): void
    {
        $admin   = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock_qty' => 10, 'price' => 50]);

        $this->post(route('pos.cart.add'), ['product_id' => $product->id, 'qty' => 4]);
        $this->post(route('pos.checkout'));

        $sale = Sale::first();
        $this->assertSame(6, $product->fresh()->stock_qty);

        $this->actingAs($admin)
             ->post(route('sales.void', $sale), ['void_reason' => 'ทดสอบยกเลิกบิล'])
             ->assertRedirect(route('sales.show', $sale));

        $this->assertSame('void', $sale->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock_qty);
    }

    public function test_พนักงานขายเข้าหน้ารายงานไม่ได้(): void
    {
        $this->get(route('reports.index'))->assertForbidden();
    }
}
