@extends('layouts.app')
@section('title', 'ใบเสร็จรับเงิน')
@section('subtitle', 'บิลเลขที่ ' . $sale->sale_no)

@section('content')
<div class="no-print mb-3 d-flex gap-2">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>พิมพ์ใบเสร็จ</button>
    <a href="{{ route('pos.index') }}" class="btn btn-success"><i class="bi bi-basket3 me-1"></i>ขายบิลใหม่</a>
    <a href="{{ route('sales.show', $sale) }}" class="btn btn-light"><i class="bi bi-info-circle me-1"></i>รายละเอียดบิล</a>
    <a href="{{ route('sales.index') }}" class="btn btn-light">ประวัติการขาย</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="receipt">
            <div class="center">
                <div style="font-size:15px;font-weight:700">{{ \App\Models\Setting::get('shop_name', config('app.shop.name')) }}</div>
                <div>{{ \App\Models\Setting::get('shop_address', config('app.shop.address')) }}</div>
                <div>โทร. {{ \App\Models\Setting::get('shop_phone', config('app.shop.phone')) }}</div>
                <div>เลขประจำตัวผู้เสียภาษี {{ \App\Models\Setting::get('shop_tax_id', config('app.shop.tax_id')) }}</div>
            </div>

            <hr>
            <div class="center" style="font-weight:700">ใบเสร็จรับเงิน / ใบกำกับภาษีอย่างย่อ</div>
            <hr>

            <table>
                <tr><td>เลขที่บิล</td><td style="text-align:right">{{ $sale->sale_no }}</td></tr>
                <tr><td>วันที่</td><td style="text-align:right">{{ ($sale->sold_at ?? $sale->created_at)->format('d/m/') }}{{ ($sale->sold_at ?? $sale->created_at)->year + 543 }} {{ ($sale->sold_at ?? $sale->created_at)->format('H:i') }}</td></tr>
                <tr><td>พนักงาน</td><td style="text-align:right">{{ $sale->user->name ?? '-' }}</td></tr>
                <tr><td>ลูกค้า</td><td style="text-align:right">{{ $sale->customer_name }}</td></tr>
                @if($sale->member)
                    <tr><td>รหัสสมาชิก</td><td style="text-align:right">{{ $sale->member->member_code }}</td></tr>
                @endif
            </table>

            <hr>

            <table>
                @foreach($sale->items as $item)
                    <tr>
                        <td colspan="2">{{ $item->product_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding-left:8px">
                            {{ $item->qty }} x {{ number_format($item->price, 2) }}
                            @if($item->discount > 0) (ลด {{ number_format($item->discount, 2) }}) @endif
                        </td>
                        <td style="text-align:right">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </table>

            <hr>

            <table>
                <tr><td>รวม {{ $sale->total_qty }} ชิ้น</td><td style="text-align:right">{{ number_format($sale->subtotal, 2) }}</td></tr>
                @if($sale->member_discount > 0)
                    <tr><td>ส่วนลดสมาชิก</td><td style="text-align:right">-{{ number_format($sale->member_discount, 2) }}</td></tr>
                @endif
                @if($sale->discount > 0)
                    <tr><td>ส่วนลดท้ายบิล</td><td style="text-align:right">-{{ number_format($sale->discount, 2) }}</td></tr>
                @endif
                <tr style="font-weight:700;font-size:14px">
                    <td>ยอดสุทธิ</td><td style="text-align:right">{{ number_format($sale->total, 2) }}</td>
                </tr>
                <tr><td>มูลค่าสินค้าก่อน VAT</td><td style="text-align:right">{{ number_format($sale->total - $sale->vat, 2) }}</td></tr>
                <tr><td>ภาษีมูลค่าเพิ่ม {{ \App\Models\Setting::vatRate() }}%</td><td style="text-align:right">{{ number_format($sale->vat, 2) }}</td></tr>
            </table>

            <hr>

            <table>
                @foreach($sale->payments as $p)
                    <tr><td>{{ $p->method_name }}</td><td style="text-align:right">{{ number_format($p->amount, 2) }}</td></tr>
                    @if($p->method === 'cash' && $p->change_amount > 0)
                        <tr><td style="padding-left:8px">รับเงิน / เงินทอน</td>
                            <td style="text-align:right">{{ number_format($p->received, 2) }} / {{ number_format($p->change_amount, 2) }}</td></tr>
                    @endif
                    @if($p->reference_no)
                        <tr><td style="padding-left:8px">อ้างอิง</td><td style="text-align:right">{{ $p->reference_no }}</td></tr>
                    @endif
                @endforeach
            </table>

            @if($sale->member)
                <hr>
                <table>
                    <tr><td>แต้มที่ได้รับบิลนี้</td><td style="text-align:right">{{ number_format($sale->points_earned) }}</td></tr>
                    <tr><td>แต้มสะสมคงเหลือ</td><td style="text-align:right">{{ number_format($sale->member->points) }}</td></tr>
                </table>
            @endif

            <hr>
            <div class="center">
                <div>{{ \App\Models\Setting::get('receipt_note', 'ขอบคุณที่ใช้บริการ') }}</div>
                <div style="margin-top:4px">*** {{ $sale->sale_no }} ***</div>
            </div>
        </div>
    </div>
</div>
@endsection
