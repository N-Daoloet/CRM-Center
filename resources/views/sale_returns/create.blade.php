<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกคืนสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card form-card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">บันทึกคืนสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">เลือกเลขขายและระบุจำนวนที่คืน</p>
                    </div>
                    <a href="{{ route('sale-returns.index') }}" class="btn secondary">กลับหน้าคืนสินค้า</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                @if($sales->count() === 0)
                    <div class="alert warning">ยังไม่มีรายการขายที่สามารถคืนสินค้าได้</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('sale-returns.store') }}">
                    @csrf

                    <div class="form-grid">
                        <label class="full-span">อ้างอิงรายการขาย
                            <select name="sale_id" required>
                                <option value="">เลือกรายการขาย</option>
                                @foreach($sales as $sale)
                                    <option value="{{ $sale->id }}" {{ old('sale_id', $selectedSaleId ?? '') == $sale->id ? 'selected' : '' }}>
                                        #{{ $sale->id }} | {{ $sale->customer->name ?? '-' }} | {{ $sale->return_product_name ?? ($sale->product->name ?? '-') }} | ขาย {{ $sale->quantity }} | คืนได้ {{ $sale->returnable_quantity }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>จำนวนคืน
                            <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required>
                        </label>

                        <label>วันที่คืน
                            <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required>
                        </label>

                        <label class="full-span">เหตุผลการคืน
                            <textarea rows="4" name="reason" placeholder="เช่น สินค้ามีตำหนิ / ส่งผิดรายการ">{{ old('reason') }}</textarea>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('sale-returns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn" {{ $sales->count() === 0 ? 'disabled' : '' }}>บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
