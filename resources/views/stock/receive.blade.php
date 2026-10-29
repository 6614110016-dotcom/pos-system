@extends('layouts.app')
@section('title', 'รับสินค้าเข้าคลัง')
@section('subtitle', 'บันทึกการรับสินค้าจากผู้จำหน่ายเข้าสต็อก (เพิ่มได้หลายรายการต่อครั้ง)')

@section('content')
<form method="POST" action="{{ route('stock.receive') }}">
    @csrf
    <div class="card">
        <div class="card-header"><i class="bi bi-box-arrow-in-down me-2"></i>ข้อมูลการรับสินค้า</div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">เลขที่เอกสารอ้างอิง</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" class="form-control"
                           placeholder="เว้นว่างเพื่อให้ระบบสร้างให้">
                </div>
                <div class="col-md-8">
                    <label class="form-label">หมายเหตุ</label>
                    <input type="text" name="note" value="{{ old('note') }}" class="form-control"
                           placeholder="เช่น รับจากบริษัท ABC จำกัด">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:45%">สินค้า</th>
                            <th style="width:15%" class="text-center">คงเหลือปัจจุบัน</th>
                            <th style="width:15%">จำนวนที่รับเข้า</th>
                            <th style="width:18%">ราคาทุนใหม่ (ถ้าเปลี่ยน)</th>
                            <th style="width:7%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                </table>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                <i class="bi bi-plus-lg me-1"></i>เพิ่มรายการ
            </button>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>บันทึกการรับเข้า</button>
            <a href="{{ route('stock.index') }}" class="btn btn-light">ยกเลิก</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const products = {{ \Illuminate\Support\Js::from($products->map(fn ($p) => [
        'id' => $p->id, 'code' => $p->product_code, 'name' => $p->name,
        'stock' => $p->stock_qty, 'unit' => $p->unit, 'cost' => (float) $p->cost_price,
    ])) }};

    let rowIndex = 0;

    function addRow(preselectId = null) {
        const i = rowIndex++;
        const options = products.map(p =>
            `<option value="${p.id}" data-stock="${p.stock}" data-unit="${p.unit}" data-cost="${p.cost}"
             ${preselectId == p.id ? 'selected' : ''}>${p.code} - ${p.name}</option>`).join('');

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${i}][product_id]" class="form-select" required onchange="syncRow(this)">
                    <option value="">- เลือกสินค้า -</option>${options}
                </select>
            </td>
            <td class="text-center stock-cell text-muted">-</td>
            <td><input type="number" name="items[${i}][qty]" min="1" value="1" class="form-control text-end" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${i}][cost_price]" class="form-control text-end" placeholder="ไม่เปลี่ยน"></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
        document.getElementById('itemsBody').appendChild(tr);

        if (preselectId) syncRow(tr.querySelector('select'));
    }

    function syncRow(select) {
        const opt = select.selectedOptions[0];
        const tr  = select.closest('tr');
        tr.querySelector('.stock-cell').textContent = opt.value
            ? `${opt.dataset.stock} ${opt.dataset.unit}` : '-';
        const costInput = tr.querySelector('input[name*="cost_price"]');
        if (opt.value) costInput.placeholder = 'เดิม ' + parseFloat(opt.dataset.cost).toFixed(2);
    }

    addRow(@json($productId));
</script>
@endpush
