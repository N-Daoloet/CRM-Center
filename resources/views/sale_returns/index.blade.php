<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คืนสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">คืนสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">รายการคืนสินค้าจากการขาย</p>
                    </div>
                    <div class="action-buttons">
                        <a href="{{ route('sale-returns.report') }}" class="btn secondary">รายงานคืนสินค้า</a>
                        <a href="{{ route('sale-returns.create') }}" class="btn">+ บันทึกคืนสินค้า</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>เลขขาย</th>
                            <th>ลูกค้า</th>
                            <th>สินค้า</th>
                            <th>จำนวนคืน</th>
                            <th>ราคา/หน่วย</th>
                            <th>จำนวนเงินคืน</th>
                            <th>วันที่คืน</th>
                            <th>เหตุผล</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td>{{ $return->id }}</td>
                                <td>{{ $return->sale_id }}</td>
                                <td>{{ $return->customer->name ?? '-' }}</td>
                                <td>{{ $return->product->name ?? '-' }}</td>
                                <td>{{ $return->quantity }}</td>
                                <td>{{ number_format($return->unit_price, 2) }}</td>
                                <td>{{ number_format($return->total_amount, 2) }}</td>
                                <td>{{ $return->return_date }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($return->reason ?? '-', 40) }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('sale-returns.edit', $return) }}" class="btn secondary small">แก้ไข</a>
                                        <form method="POST" action="{{ route('sale-returns.destroy', $return) }}" onsubmit="return confirm('ยืนยันลบรายการคืนสินค้า?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn secondary small">ลบ</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- <tr>
                                <td colspan="10">ยังไม่มีข้อมูลคืนสินค้า</td>
                            </tr> -->
                        @endforelse
                    </tbody>
                </table>

            </div>
        </main>
    </div>
    @include('partials.datatables')
</body>
</html>
