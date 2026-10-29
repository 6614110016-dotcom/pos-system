<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>แสดงรายงาน</button>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i>พิมพ์</button>
                @isset($exportUrl)
                    <a href="{{ $exportUrl }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>ส่งออก CSV</a>
                @endisset
                <a href="{{ route('reports.index') }}" class="btn btn-light ms-auto">เมนูรายงาน</a>
            </div>
        </form>
    </div>
</div>
