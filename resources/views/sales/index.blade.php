<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">รายการขาย</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ดูประวัติการขายจากฐานข้อมูลจริง</p>
                    </div>
                    <a href="{{ route('sales.create') }}" class="btn">+ เพิ่มการขาย</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <!-- <div class="list-toolbar">
                    <div class="list-title-block">
                        <h3 style="margin:0;">รายการขาย</h3>
                        <p style="margin:4px 0 0; color:#6b7280;">เรียกดูประวัติการขายในตารางที่รองรับการค้นหาและเรียงลำดับ</p>
                    </div>
                    <a href="{{ route('sales.create') }}" class="btn">+ เพิ่มการขาย</a>
                </div> -->

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ลูกค้า</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ราคา/หน่วย</th>
                            <th>จำนวนเงิน</th>
                            <th>วันที่</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $returnable = max(0, $sale->quantity - (int) ($sale->returned_quantity ?? 0));
                            @endphp
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->customer->name ?? '-' }}</td>
                                <td>{{ $sale->product->name ?? '-' }}</td>
                                <td>{{ $sale->quantity }}</td>
                                <td>{{ number_format($sale->unit_price, 2) }}</td>
                                <td>{{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ $sale->sale_date }}</td>
                                <td>{{ $sale->status }}</td>
                                <td>
                                    @if($returnable > 0)
                                        <a href="{{ route('sale-returns.create', ['sale_id' => $sale->id]) }}" class="btn secondary small">คืนสินค้า</a>
                                    @else
                                        <span class="btn secondary small" style="opacity:.65; pointer-events:none;">คืนครบแล้ว</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">ยังไม่มีข้อมูลการขาย</td>
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
