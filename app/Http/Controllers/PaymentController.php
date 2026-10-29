<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * โมดูลจ่ายเงิน (Payment)
 * - รับชำระเงินสด / โอน / พร้อมเพย์ / บัตร / e-Wallet
 * - รองรับการแยกชำระหลายช่องทางในบิลเดียว
 * - เมื่อชำระครบ -> ปิดบิล สะสมแต้มให้สมาชิก และออกใบเสร็จ
 */
class PaymentController extends Controller
{
    /** รายการรับชำระเงินทั้งหมด */
    public function index(Request $request): View
    {
        $payments = Payment::with(['sale.member', 'user'])
            ->when($request->method, fn ($q, $m) => $q->where('method', $m))
            ->when($request->q, fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('payment_no', 'like', "%{$term}%")
                    ->orWhereHas('sale', fn ($s) => $s->where('sale_no', 'like', "%{$term}%"))
            ))
            ->when($request->from && $request->to, fn ($q) => $q->whereBetween('paid_at', [
                $request->from.' 00:00:00',
                $request->to.' 23:59:59',
            ]))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        // สรุปยอดรับชำระแยกตามช่องทาง
        $byMethod = Payment::success()
            ->when($request->from && $request->to, fn ($q) => $q->whereBetween('paid_at', [
                $request->from.' 00:00:00',
                $request->to.' 23:59:59',
            ]))
            ->selectRaw('method, COUNT(*) as cnt, SUM(amount) as total')
            ->groupBy('method')
            ->get();

        return view('payments.index', compact('payments', 'byMethod'));
    }

    /** หน้าจอรับชำระเงินของบิล */
    public function create(Sale $sale): View
    {
        if ($sale->status === 'void') {
            abort(404, 'บิลนี้ถูกยกเลิกแล้ว');
        }

        $sale->load(['items', 'payments', 'member']);

        return view('payments.create', [
            'sale'    => $sale,
            'methods' => Payment::METHODS,
        ]);
    }

    /** บันทึกการรับชำระเงิน */
    public function store(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status === 'paid') {
            return redirect()->route('sales.receipt', $sale)->with('error', 'บิลนี้ชำระเงินเรียบร้อยแล้ว');
        }

        if ($sale->status === 'void') {
            return back()->with('error', 'ไม่สามารถรับชำระบิลที่ถูกยกเลิกแล้วได้');
        }

        $data = $request->validate([
            'method'       => ['required', Rule::in(array_keys(Payment::METHODS))],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'received'     => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'slip'         => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'method.required' => 'กรุณาเลือกช่องทางการชำระเงิน',
            'amount.required' => 'กรุณาระบุจำนวนเงินที่ชำระ',
            'amount.min'      => 'จำนวนเงินต้องมากกว่า 0',
            'slip.image'      => 'ไฟล์สลิปต้องเป็นรูปภาพ',
        ]);

        $outstanding = $sale->outstanding;

        if ($data['amount'] > $outstanding + 0.01) {
            return back()->with('error', 'จำนวนเงินที่ชำระเกินยอดค้างชำระ ('.number_format($outstanding, 2).' บาท)');
        }

        // เงินสด: ต้องรับเงินมาไม่น้อยกว่ายอดที่ชำระ เพื่อคำนวณเงินทอน
        $received = (float) ($data['received'] ?? $data['amount']);

        if ($data['method'] === 'cash' && $received < $data['amount']) {
            return back()->with('error', 'จำนวนเงินที่รับมาน้อยกว่ายอดที่ต้องชำระ');
        }

        $change = $data['method'] === 'cash' ? round($received - $data['amount'], 2) : 0;

        // อัปโหลดสลิปโอนเงิน (ถ้ามี)
        $slipName = null;
        if ($request->hasFile('slip')) {
            $slipName = 'slip_'.time().'_'.uniqid().'.'.$request->file('slip')->getClientOriginalExtension();
            $request->file('slip')->move(public_path('uploads/slips'), $slipName);
        }

        DB::transaction(function () use ($sale, $data, $received, $change, $slipName) {
            $sale->payments()->create([
                'payment_no'    => Payment::generatePaymentNo(),
                'method'        => $data['method'],
                'amount'        => $data['amount'],
                'received'      => $received,
                'change_amount' => $change,
                'reference_no'  => $data['reference_no'] ?? null,
                'slip_image'    => $slipName,
                'status'        => 'success',
                'user_id'       => auth()->id(),
                'paid_at'       => now(),
            ]);

            $sale->refresh();

            // ชำระครบแล้ว -> ปิดบิล
            if ($sale->outstanding <= 0.01) {
                $sale->update([
                    'status'  => 'paid',
                    'sold_at' => now(),
                ]);

                // สะสมแต้มและยอดซื้อให้สมาชิก
                if ($member = $sale->member) {
                    $member->increment('points', $sale->points_earned);
                    $member->increment('total_spent', $sale->total);
                    $member->refresh()->refreshTier();
                }
            }
        });

        $sale->refresh();

        if ($sale->status === 'paid') {
            ActivityLog::record('create', 'Payment', "รับชำระเงินบิล {$sale->sale_no} ครบถ้วน");

            return redirect()->route('sales.receipt', $sale)
                ->with('success', 'รับชำระเงินครบถ้วนแล้ว'.($change > 0 ? ' เงินทอน '.number_format($change, 2).' บาท' : ''));
        }

        ActivityLog::record('create', 'Payment', "รับชำระเงินบางส่วนบิล {$sale->sale_no}");

        return redirect()->route('payments.create', $sale)
            ->with('success', 'บันทึกการชำระเงินแล้ว ยอดค้างชำระคงเหลือ '.number_format($sale->outstanding, 2).' บาท');
    }

    /** ดูรายละเอียดการชำระเงิน */
    public function show(Payment $payment): View
    {
        $payment->load(['sale.items', 'sale.member', 'user']);

        return view('payments.show', compact('payment'));
    }

    /** ยกเลิกรายการชำระเงิน (เฉพาะผู้จัดการขึ้นไป) */
    public function destroy(Payment $payment): RedirectResponse
    {
        $sale = $payment->sale;

        if ($sale->status === 'paid') {
            return back()->with('error', 'บิลปิดการขายแล้ว หากต้องการแก้ไขกรุณายกเลิกทั้งบิลแทน');
        }

        $no = $payment->payment_no;
        $payment->delete();
        ActivityLog::record('delete', 'Payment', "ลบรายการชำระเงิน {$no}");

        return back()->with('success', "ลบรายการชำระเงิน {$no} เรียบร้อยแล้ว");
    }
}
