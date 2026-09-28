<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">เพิ่มสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">สร้างรายการสินค้าใหม่ในระบบ</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn secondary">กลับหน้าสินค้า</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-grid">
                        <label>รหัสสินค้า 
                            <input type="text" value="{{ $nextSku }}" readonly>
                        </label>

                        <label>ชื่อสินค้า
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="ระบุชื่อสินค้า" required>
                        </label>

                        <label>ราคาจริง
                            <input type="number" step="0.01" name="real_price" value="{{ old('real_price') }}" placeholder="ระบุราคาจริง" min="0" required>
                        </label>

                        <label>ราคาขาย
                            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" placeholder="ระบุราคาขาย" min="0" required>
                        </label>
                        

                        <label>คงเหลือ
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required>
                        </label>

                        <label>จุดเตือนสต็อกต่ำ
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0" required>
                        </label>

                        <label>สถานะสินค้า
                            <select name="is_active" required>
                                <option value="1" {{ (string) old('is_active', '1') === '1' ? 'selected' : '' }}>Active — พร้อมใช้งาน</option>
                                <option value="0" {{ (string) old('is_active', '1') === '0' ? 'selected' : '' }}>Inactive — ปิดใช้งาน</option>
                            </select>
                        </label>

                        <label>รูปภาพสินค้า
                            <input type="file" name="images[]" accept="image/*" multiple data-image-preview-input>
                            <small class="form-note">เลือกได้หลายรูป (ไม่เกิน 10 รูป รูปละไม่เกิน 2 MB)</small>
                        </label>
                        <div class="full-span product-selected-previews" data-image-preview aria-live="polite" hidden></div>

                        <label class="full-span">คำอธิบาย
                            <textarea rows="4" name="description" placeholder="ระบุรายละเอียดสินค้า">{{ old('description') }}</textarea>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('products.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/product-image-preview.js') }}" defer></script>
</body>
</html>
