<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">สินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">รายการสินค้าที่มีในระบบ</p>
                    </div>
                    <a href="{{ route('products.create') }}" class="btn">+ เพิ่มสินค้า</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                @if($products->count() > 0)
                    @php
                        $lowStockCount = $products->filter(fn($item) => $item->isLowStock())->count();
                    @endphp
                    @if($lowStockCount > 0)
                        <div class="alert warning">มีสินค้าใกล้หมด {{ $lowStockCount }} รายการ</div>
                    @endif
                @endif

                <!-- <div class="list-toolbar">
                    <div class="list-title-block">
                        <h3 style="margin:0;">รายการสินค้า</h3>
                        <p style="margin:4px 0 0; color:#6b7280;">จัดการข้อมูลสินค้าในตารางที่รองรับการเรียงลำดับ</p>
                    </div>
                    <a href="{{ route('products.create') }}" class="btn">+ เพิ่มสินค้า</a>
                </div> -->

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>รูปสินค้า</th>
                            <th>SKU</th>
                            <th>ชื่อสินค้า</th>
                            <th>สถานะ</th>
                            <th>ราคาจริง</th>
                            <th>ราคาขาย</th>
                            <th>คงเหลือ</th>
                            <!-- <th>คำอธิบาย</th> -->
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>
                                    @if($product->image_path)
                                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-table-image">
                                    @else
                                        <div class="product-thumb">{{ strtoupper(substr($product->name, 0, 1)) }}</div>
                                    @endif
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>
                                    <span class="product-status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ number_format($product->real_price ?? $product->price, 2) }}</td>
                                <td>{{ number_format($product->sale_price ?? $product->price, 2) }}</td>
                                <td>
                                    <strong>{{ $product->stock }}</strong>
                                    @if($product->isLowStock())
                                        <span class="stock-badge low">เหลือน้อย</span>
                                    @endif
                                </td>
                                <!-- <td>{{ Str::limit($product->description ?? '-', 40) }}</td> -->
                                <td>
                                    <a href="{{ route('products.edit', $product) }}" class="btn secondary small">แก้ไข</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">ยังไม่มีข้อมูลสินค้า</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </main>
    </div>
    @include('partials.datatables')
</body>
</html>
