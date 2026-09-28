<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ปรับปรุงข้อมูลสินค้าและสต็อก</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn secondary">กลับหน้าสินค้า</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        <label>รหัสสินค้า
                            <input type="text" value="{{ $product->sku }}" readonly>
                        </label>

                        <label>ชื่อสินค้า
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                        </label>

                        <label>ราคาจริง
                            <input type="number" step="0.01" name="real_price" value="{{ old('real_price', $product->real_price ?? $product->price) }}" min="0" required>
                        </label>

                        <label>ราคาขาย
                            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? $product->price) }}" min="0" required>
                        </label>

                        <label>คงเหลือปัจจุบัน
                            <input type="number" value="{{ $product->stock }}" readonly>
                        </label>

                        <label>จุดเตือนสต็อกต่ำ
                            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" required>
                        </label>

                        <label>สถานะสินค้า
                            <select name="is_active" required>
                                <option value="1" {{ (string) old('is_active', $product->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active — พร้อมใช้งาน</option>
                                <option value="0" {{ (string) old('is_active', $product->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive — ปิดใช้งาน</option>
                            </select>
                        </label>

                        <label>เพิ่มรูปสินค้าใหม่
                            <input type="file" name="images[]" accept="image/*" multiple data-image-preview-input>
                            <small class="form-note">เลือกเพิ่มได้หลายรูป (ไม่เกิน 10 รูป รูปละไม่เกิน 2 MB)</small>
                        </label>
                        <div class="full-span product-selected-previews" data-image-preview aria-live="polite" hidden></div>

                        @if($product->images->isNotEmpty() || $product->image_path)
                            <div class="full-span product-image-preview-wrap">
                                @if($product->image_path)
                                    <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-image-preview">
                                @endif
                                @foreach($product->images as $image)
                                    @if($image->image_path !== $product->image_path)
                                        <img src="{{ asset($image->image_path) }}" alt="{{ $product->name }}" class="product-image-preview">
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <label class="full-span">คำอธิบาย
                            <textarea rows="4" name="description">{{ old('description', $product->description) }}</textarea>
                        </label>
                    </div>

                    <div class="stock-card">
                        <h4>ปรับสต็อก</h4>
                        <div class="stock-control">
                            <label>จำนวนเพิ่มเข้า
                                <input type="number" name="stock_in" value="{{ old('stock_in', 0) }}" min="0">
                            </label>
                            <label>จำนวนตัดออก
                                <input type="number" name="stock_out" value="{{ old('stock_out', 0) }}" min="0">
                            </label>
                            <div>
                                <small class="form-note">สต็อกใหม่ = คงเหลือ + เพิ่มเข้า - ตัดออก</small>
                            </div>
                        </div>
                        <div class="stock-row">
                            <span>คงเหลือปัจจุบัน</span>
                            <strong>{{ $product->stock }}</strong>
                        </div>
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
