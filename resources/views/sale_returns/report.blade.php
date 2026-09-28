<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานคืนสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">รายงานสรุปยอดคืนสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">สรุปแบบรายวัน รายเดือน และตามสินค้า</p>
                    </div>
                    <a href="{{ route('sale-returns.index') }}" class="btn secondary">กลับหน้าคืนสินค้า</a>
                </div>

                <form method="GET" class="modal-form" style="margin-bottom: 16px;">
                    <div class="form-grid">
                        <label>จากวันที่
                            <input type="date" name="from" value="{{ $from }}">
                        </label>
                        <label>ถึงวันที่
                            <input type="date" name="to" value="{{ $to }}">
                        </label>
                    </div>
                    <div class="modal-actions">
                        <a href="{{ route('sale-returns.report') }}" class="btn secondary">ล้างตัวกรอง</a>
                        <button type="submit" class="btn">ค้นหา</button>
                    </div>
                </form>

                <div class="stats-grid" style="margin-top: 0; margin-bottom: 18px;">
                    <div class="stat-card">
                        <div class="stat-label">จำนวนรายการคืน</div>
                        <div class="stat-value">{{ number_format((int) ($summary->total_returns ?? 0)) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">จำนวนสินค้าที่คืน</div>
                        <div class="stat-value">{{ number_format((int) ($summary->total_qty ?? 0)) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">ยอดเงินคืนรวม</div>
                        <div class="stat-value">{{ number_format((float) ($summary->total_amount ?? 0), 2) }}</div>
                    </div>
                </div>

                <h3 style="margin: 0 0 8px;">สรุปรายวัน</h3>
                <table class="data-table" style="margin-bottom: 16px;">
                    <thead>
                        <tr>
                            <th>วันที่คืน</th>
                            <th>จำนวนรายการ</th>
                            <th>จำนวนชิ้น</th>
                            <th>ยอดเงินคืน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailySummary as $row)
                            <tr>
                                <td>{{ $row->return_date }}</td>
                                <td>{{ number_format((int) $row->total_returns) }}</td>
                                <td>{{ number_format((int) $row->total_qty) }}</td>
                                <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <h3 style="margin: 0 0 8px;">สรุปรายเดือน</h3>
                <table class="data-table" style="margin-bottom: 16px;">
                    <thead>
                        <tr>
                            <th>เดือน</th>
                            <th>จำนวนรายการ</th>
                            <th>จำนวนชิ้น</th>
                            <th>ยอดเงินคืน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlySummary as $row)
                            <tr>
                                <td>{{ $row->return_month }}</td>
                                <td>{{ number_format((int) $row->total_returns) }}</td>
                                <td>{{ number_format((int) $row->total_qty) }}</td>
                                <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <h3 style="margin: 0 0 8px;">สรุปตามสินค้า</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th>จำนวนรายการ</th>
                            <th>จำนวนชิ้น</th>
                            <th>ยอดเงินคืน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productSummary as $row)
                            <tr>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ number_format((int) $row->total_returns) }}</td>
                                <td>{{ number_format((int) $row->total_qty) }}</td>
                                <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
