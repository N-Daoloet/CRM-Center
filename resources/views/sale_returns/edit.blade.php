<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขคืนสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card form-card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขรายการคืนสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">อ้างอิงเลขขาย #{{ $saleReturn->sale_id }}</p>
                    </div>
                    <a href="{{ route('sale-returns.index') }}" class="btn secondary">กลับหน้าคืนสินค้า</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('sale-returns.update', $saleReturn) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <label>ลูกค้า
                            <input type="text" value="{{ $saleReturn->customer->name ?? '-' }}" readonly>
                        </label>

                        <label>สินค้า
                            <input type="text" value="{{ $saleReturn->product->name ?? '-' }}" readonly>
                        </label>

                        <label>จำนวนขาย
                            <input type="number" value="{{ $saleReturn->sale->quantity }}" readonly>
                        </label>

                        <label>คืนได้สูงสุด
                            <input type="number" value="{{ $returnableQuantity }}" readonly>
                        </label>

                        <label>จำนวนคืน
                            <input type="number" name="quantity" min="1" max="{{ $returnableQuantity }}" value="{{ old('quantity', $saleReturn->quantity) }}" required>
                        </label>

                        <label>วันที่คืน
                            <input type="date" name="return_date" value="{{ old('return_date', $saleReturn->return_date) }}" required>
                        </label>

                        <label class="full-span">เหตุผลการคืน
                            <textarea rows="4" name="reason" placeholder="ระบุเหตุผลเพิ่มเติม">{{ old('reason', $saleReturn->reason) }}</textarea>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('sale-returns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
