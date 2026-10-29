<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| เส้นทาง (Routes) ของระบบ POS
|--------------------------------------------------------------------------
| แบ่งตามโมดูล:
|   Login | Dashboard | Member | Employee | Stock | Sale | Payment | Report | Setting
|
| สิทธิ์การใช้งาน
|   admin   = ผู้ดูแลระบบ   เข้าได้ทุกเมนู
|   manager = ผู้จัดการร้าน เข้าได้ทุกเมนูยกเว้นตั้งค่าระบบ
|   cashier = พนักงานขาย    เข้าได้เฉพาะ ขายสินค้า / สมาชิก / ดูสินค้า
*/

/* ---------------- โมดูลยืนยันตัวตน (Login) ---------------- */
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/change-password', [LoginController::class, 'showChangePassword'])->name('password.change');
    Route::put('/change-password', [LoginController::class, 'changePassword'])->name('password.update');

    /* ---------------- แดชบอร์ด ---------------- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* ---------------- โมดูลการขาย (Sale) : หน้าจอ POS ---------------- */
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [SaleController::class, 'pos'])->name('index');
        Route::post('/cart/add', [SaleController::class, 'addToCart'])->name('cart.add');
        Route::post('/cart/update', [SaleController::class, 'updateCart'])->name('cart.update');
        Route::post('/cart/item-discount', [SaleController::class, 'itemDiscount'])->name('cart.item-discount');
        Route::post('/cart/remove', [SaleController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/cart/clear', [SaleController::class, 'clearCart'])->name('cart.clear');
        Route::post('/member', [SaleController::class, 'setMember'])->name('member');
        Route::post('/discount', [SaleController::class, 'setDiscount'])->name('discount');
        Route::post('/checkout', [SaleController::class, 'checkout'])->name('checkout');
        Route::get('/search', [SaleController::class, 'quickSearch'])->name('search');
        Route::get('/search-member', [MemberController::class, 'search'])->name('search-member');
    });

    /* ---------------- โมดูลการขาย (Sale) : ประวัติการขาย ---------------- */
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::post('/sales/{sale}/void', [SaleController::class, 'void'])
        ->middleware('role:admin,manager')->name('sales.void');

    /* ---------------- โมดูลจ่ายเงิน (Payment) ---------------- */
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/sale/{sale}', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/sale/{sale}', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])
        ->middleware('role:admin,manager')->name('payments.destroy');

    /* ---------------- โมดูลสมาชิก (Member) ---------------- */
    Route::resource('members', MemberController::class);

    /* ---------------- โมดูลสินค้า (Stock) ---------------- */
    Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/receive', [StockController::class, 'receiveForm'])->name('receive.form');
        Route::post('/receive', [StockController::class, 'receive'])->name('receive');
        Route::get('/movements', [StockController::class, 'movements'])->name('movements');
        Route::get('/{product}/adjust', [StockController::class, 'adjustForm'])->name('adjust.form');
        Route::put('/{product}/adjust', [StockController::class, 'adjust'])->name('adjust');
    });

    /* ---------------- โมดูลพนักงาน (Employee) : เฉพาะ admin / manager ---------------- */
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::patch('/employees/{employee}/toggle', [EmployeeController::class, 'toggleStatus'])
            ->name('employees.toggle');

        /* ---------------- โมดูลรายงานการขาย (Report) ---------------- */
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/employees', [ReportController::class, 'employees'])->name('employees');
            Route::get('/members', [ReportController::class, 'members'])->name('members');
            Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
            Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        });
    });

    /* ---------------- โมดูลอื่นๆ : ตั้งค่าระบบ (เฉพาะ admin) ---------------- */
    Route::middleware('role:admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
        Route::get('/logs', [SettingController::class, 'logs'])->name('logs');
    });
});
