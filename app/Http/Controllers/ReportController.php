<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * โมดูลรายงานการขาย (Report)
 * 1. รายงานสรุปยอดขายรายวัน
 * 2. รายงานสินค้าขายดี / กำไรต่อสินค้า
 * 3. รายงานยอดขายแยกตามพนักงาน
 * 4. รายงานสมาชิกและยอดซื้อสะสม
 * 5. รายงานสต็อกคงเหลือและมูลค่าสินค้า
 * 6. รายงานช่องทางการชำระเงิน
 */
class ReportController extends Controller
{
    /** เมนูรวมรายงาน */
    public function index(): View
    {
        return view('reports.index');
    }

    /** ช่วงวันที่จาก request (ค่าเริ่มต้น = เดือนปัจจุบัน) */
    protected function range(Request $request): array
    {
        return [
            $request->input('from', today()->startOfMonth()->toDateString()),
            $request->input('to', today()->toDateString()),
        ];
    }

    /* ---------------- 1) รายงานยอดขายรายวัน ---------------- */
    public function sales(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $daily = Sale::paid()->between($from, $to)
            ->selectRaw('DATE(sold_at) as date, COUNT(*) as bills, SUM(subtotal) as subtotal,
                         SUM(discount + member_discount) as discount, SUM(vat) as vat, SUM(total) as total')
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy('date')
            ->get();

        $summary = [
            'bills'    => (int) $daily->sum('bills'),
            'subtotal' => (float) $daily->sum('subtotal'),
            'discount' => (float) $daily->sum('discount'),
            'vat'      => (float) $daily->sum('vat'),
            'total'    => (float) $daily->sum('total'),
        ];

        $summary['avg_bill'] = $summary['bills'] > 0 ? $summary['total'] / $summary['bills'] : 0;

        // กำไรขั้นต้นในช่วงเวลา
        $summary['profit'] = (float) SaleItem::whereHas('sale', fn ($q) => $q->paid()->between($from, $to))
            ->selectRaw('SUM((price - cost_price) * qty - discount) as profit')
            ->value('profit');

        $voided = Sale::where('status', 'void')
            ->whereBetween('voided_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->count();

        return view('reports.sales', compact('daily', 'summary', 'from', 'to', 'voided'));
    }

    /* ---------------- 2) รายงานสินค้าขายดี ---------------- */
    public function products(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = SaleItem::whereHas('sale', fn ($q) => $q->paid()->between($from, $to))
            ->selectRaw('product_id, product_code, product_name,
                         SUM(qty) as total_qty,
                         SUM(subtotal) as total_amount,
                         SUM((price - cost_price) * qty - discount) as profit')
            ->groupBy('product_id', 'product_code', 'product_name')
            ->orderByDesc('total_qty')
            ->get();

        $summary = [
            'items'  => $rows->count(),
            'qty'    => (int) $rows->sum('total_qty'),
            'amount' => (float) $rows->sum('total_amount'),
            'profit' => (float) $rows->sum('profit'),
        ];

        return view('reports.products', compact('rows', 'summary', 'from', 'to'));
    }

    /* ---------------- 3) รายงานยอดขายตามพนักงาน ---------------- */
    public function employees(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = Sale::paid()->between($from, $to)
            ->selectRaw('user_id, COUNT(*) as bills, SUM(total) as total, AVG(total) as avg_bill')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user')
            ->get();

        $summary = [
            'bills' => (int) $rows->sum('bills'),
            'total' => (float) $rows->sum('total'),
            'staff' => $rows->count(),
        ];

        return view('reports.employees', compact('rows', 'summary', 'from', 'to'));
    }

    /* ---------------- 4) รายงานสมาชิก ---------------- */
    public function members(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = Member::withCount(['sales as bills' => fn ($q) => $q->paid()->between($from, $to)])
            ->withSum(['sales as period_total' => fn ($q) => $q->paid()->between($from, $to)], 'total')
            ->orderByDesc('period_total')
            ->limit(50)
            ->get();

        $summary = [
            'members'     => Member::active()->count(),
            'new_members' => Member::whereBetween('joined_at', [$from, $to])->count(),
            'period_total'=> (float) $rows->sum('period_total'),
            'points'      => (int) Member::sum('points'),
        ];

        return view('reports.members', compact('rows', 'summary', 'from', 'to'));
    }

    /* ---------------- 5) รายงานสต็อกคงเหลือ ---------------- */
    public function stock(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->filter === 'low', fn ($q) => $q->lowStock())
            ->when($request->filter === 'out', fn ($q) => $q->where('stock_qty', '<=', 0))
            ->orderBy('stock_qty')
            ->get();

        $summary = [
            'items'      => $products->count(),
            'qty'        => (int) $products->sum('stock_qty'),
            'cost_value' => (float) $products->sum(fn ($p) => $p->stock_qty * $p->cost_price),
            'sale_value' => (float) $products->sum(fn ($p) => $p->stock_qty * $p->price),
            'low'        => $products->where('is_low_stock', true)->count(),
        ];

        return view('reports.stock', compact('products', 'summary'));
    }

    /* ---------------- 6) รายงานช่องทางการชำระเงิน ---------------- */
    public function payments(Request $request): View
    {
        [$from, $to] = $this->range($request);

        $rows = Payment::success()
            ->whereBetween('paid_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('method, COUNT(*) as cnt, SUM(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $summary = [
            'transactions' => (int) $rows->sum('cnt'),
            'total'        => (float) $rows->sum('total'),
        ];

        return view('reports.payments', compact('rows', 'summary', 'from', 'to'));
    }

    /* ---------------- ส่งออกรายงานยอดขายเป็นไฟล์ CSV ---------------- */
    public function exportSales(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $sales = Sale::paid()->between($from, $to)->with(['member', 'user'])->orderBy('sold_at')->get();

        $filename = "sales_report_{$from}_to_{$to}.csv";

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM ให้ Excel อ่านภาษาไทยได้

            fputcsv($out, ['เลขที่บิล', 'วันที่', 'สมาชิก', 'พนักงานขาย', 'ยอดก่อนลด', 'ส่วนลด', 'VAT', 'ยอดสุทธิ']);

            foreach ($sales as $s) {
                fputcsv($out, [
                    $s->sale_no,
                    optional($s->sold_at)->format('d/m/Y H:i'),
                    $s->customer_name,
                    $s->user->name ?? '-',
                    number_format((float) $s->subtotal, 2, '.', ''),
                    number_format((float) $s->discount + (float) $s->member_discount, 2, '.', ''),
                    number_format((float) $s->vat, 2, '.', ''),
                    number_format((float) $s->total, 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
