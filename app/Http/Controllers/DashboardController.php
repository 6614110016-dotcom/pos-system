<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * หน้าแดชบอร์ด - สรุปภาพรวมของร้านในหน้าเดียว
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $today     = today();
        $monthFrom = $today->copy()->startOfMonth();

        /* ---- การ์ดสรุปยอด ---- */
        $todaySales = Sale::paid()->whereDate('sold_at', $today);
        $stats = [
            'today_total' => (float) (clone $todaySales)->sum('total'),
            'today_bills' => (clone $todaySales)->count(),
            'month_total' => (float) Sale::paid()
                                ->whereBetween('sold_at', [$monthFrom, $today->copy()->endOfDay()])
                                ->sum('total'),
            'products'      => Product::active()->count(),
            'members'       => Member::active()->count(),
            'low_stock'     => Product::active()->lowStock()->count(),
            'out_of_stock'  => Product::active()->where('stock_qty', '<=', 0)->count(),
        ];

        $stats['avg_bill'] = $stats['today_bills'] > 0
            ? $stats['today_total'] / $stats['today_bills']
            : 0;

        /* ---- กราฟยอดขาย 7 วันล่าสุด ---- */
        $chartLabels = [];
        $chartValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $chartLabels[] = $day->format('d/m');
            $chartValues[] = (float) Sale::paid()->whereDate('sold_at', $day)->sum('total');
        }

        /* ---- สินค้าขายดี 5 อันดับ (เดือนนี้) ---- */
        $topProducts = SaleItem::select(
                'product_name',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_amount')
            )
            ->whereHas('sale', fn ($q) => $q->paid()->whereBetween('sold_at', [$monthFrom, $today->copy()->endOfDay()]))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        /* ---- บิลล่าสุด ---- */
        $recentSales = Sale::with(['member', 'user'])
            ->latest('id')
            ->limit(8)
            ->get();

        /* ---- สินค้าใกล้หมด ---- */
        $lowStockProducts = Product::with('category')
            ->active()
            ->lowStock()
            ->orderBy('stock_qty')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'chartLabels', 'chartValues', 'topProducts', 'recentSales', 'lowStockProducts'
        ));
    }
}
