<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ภาพรวมการขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .overview-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid #fed7aa;
            background: radial-gradient(circle at 10% 20%, #fff7ed 0%, #ffffff 45%, #fffbf5 100%);
            padding: 16px;
            margin-bottom: 16px;
        }

        .overview-hero::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            right: -70px;
            top: -120px;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2) 0%, rgba(251, 146, 60, 0.04) 100%);
            pointer-events: none;
        }

        .overview-chart-grid {
            display: grid;
            grid-template-columns: 1.25fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .overview-chart-card {
            background: #fff;
            border: 1px solid #f3e2d3;
            border-radius: 16px;
            padding: 14px;
        }

        .overview-chart-card h3 {
            margin: 0 0 6px;
            color: #9a2c00;
        }

        .overview-subtitle {
            margin: 0 0 12px;
            color: #6b7280;
            font-size: 13px;
        }

        .rank-bars {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .rank-row {
            display: grid;
            grid-template-columns: 150px 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .rank-name {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rank-track {
            height: 10px;
            border-radius: 999px;
            background: #ffedd5;
            overflow: hidden;
        }

        .rank-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .rank-value {
            font-size: 12px;
            font-weight: 700;
            color: #9a3412;
            min-width: 88px;
            text-align: right;
        }

        .mini-columns {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 8px;
            height: 190px;
            padding-top: 8px;
        }

        .mini-col-item {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .mini-col {
            width: 100%;
            max-width: 34px;
            border-radius: 8px 8px 4px 4px;
            background: linear-gradient(180deg, #fdba74 0%, #f97316 100%);
            min-height: 12px;
        }

        .mini-col-label {
            font-size: 11px;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 52px;
            text-align: center;
        }

        @media (max-width: 980px) {
            .overview-chart-grid {
                grid-template-columns: 1fr;
            }

            .rank-row {
                grid-template-columns: 100px 1fr auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="overview-hero">
                    <div class="topbar" style="margin-bottom:0;">
                        <div>
                            <h1 style="margin:0;">ภาพรวมการขาย</h1>
                            <p style="margin: 4px 0 0; color: #6b7280;">แดชบอร์ดยอดขายพร้อมอันดับสินค้าแบบทันสมัย</p>
                        </div>
                        <a href="{{ route('sales.orders') }}" class="btn secondary">หน้ารายการขาย</a>
                    </div>
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
                        <a href="{{ route('sales.overview') }}" class="btn secondary">ล้างตัวกรอง</a>
                        <button type="submit" class="btn">ค้นหา</button>
                    </div>
                </form>

                <div class="stats-grid" style="margin-top: 0; margin-bottom: 18px;">
                    <div class="stat-card">
                        <div class="stat-label">จำนวนออเดอร์</div>
                        <div class="stat-value">{{ number_format((int) ($summary->total_orders ?? 0)) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">จำนวนสินค้าที่ขาย</div>
                        <div class="stat-value">{{ number_format((int) ($summary->total_qty ?? 0)) }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">ยอดขายรวม</div>
                        <div class="stat-value">{{ number_format((float) ($summary->total_amount ?? 0), 2) }}</div>
                    </div>
                </div>

                @php
                    $topRanking = $productRanking->take(8);
                    $maxAmount = (float) ($topRanking->max('total_amount') ?? 0);
                    $maxQty = (int) ($topRanking->max('total_qty') ?? 0);
                    $sumTopAmount = (float) ($topRanking->sum('total_amount') ?? 0);
                @endphp

                <div class="overview-chart-grid">
                    <div class="overview-chart-card">
                        <h3>กราฟสัดส่วนยอดขายตามสินค้า (Top 8)</h3>
                        <p class="overview-subtitle">เปรียบเทียบตามยอดขายรวมของสินค้าแต่ละตัว</p>
                        <div class="rank-bars">
                            @forelse($topRanking as $row)
                                @php
                                    $width = $maxAmount > 0 ? ((float) $row->total_amount / $maxAmount) * 100 : 0;
                                    $percent = $sumTopAmount > 0 ? ((float) $row->total_amount / $sumTopAmount) * 100 : 0;
                                @endphp
                                <div class="rank-row">
                                    <div class="rank-name" title="{{ $row->product_name }}">{{ $row->product_name }}</div>
                                    <div class="rank-track">
                                        <div class="rank-fill" style="width: {{ number_format($width, 2, '.', '') }}%;"></div>
                                    </div>
                                    <div class="rank-value">{{ number_format($percent, 1) }}%</div>
                                </div>
                            @empty
                                <div class="alert warning" style="margin:0;">ไม่มีข้อมูลยอดขายสินค้า</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="overview-chart-card">
                        <h3>กราฟจำนวนชิ้นที่ขาย (Top 8)</h3>
                        <p class="overview-subtitle">ดูสินค้าที่ถูกขายบ่อยตามจำนวนชิ้น</p>
                        <div class="mini-columns">
                            @forelse($topRanking as $row)
                                @php
                                    $height = $maxQty > 0 ? max(12, ((int) $row->total_qty / $maxQty) * 150) : 12;
                                @endphp
                                <div class="mini-col-item" title="{{ $row->product_name }} : {{ (int) $row->total_qty }} ชิ้น">
                                    <div class="mini-col" style="height: {{ number_format($height, 2, '.', '') }}px;"></div>
                                    <div class="mini-col-label">{{ \Illuminate\Support\Str::limit($row->product_name, 8, '') }}</div>
                                </div>
                            @empty
                                <div class="alert warning" style="margin:0; width:100%;">ไม่มีข้อมูลจำนวนขาย</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <h3 style="margin: 0 0 8px;">อันดับสินค้า (Ranking Table)</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>อันดับ</th>
                            <th>SKU</th>
                            <th>สินค้า</th>
                            <th>จำนวนออเดอร์</th>
                            <th>จำนวนชิ้น</th>
                            <th>ยอดขายรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productRanking as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->product_sku }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td>{{ number_format((int) $row->order_count) }}</td>
                                <td>{{ number_format((int) $row->total_qty) }}</td>
                                <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
