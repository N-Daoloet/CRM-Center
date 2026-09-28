<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <h1>Dashboard</h1>
                <p>ภาพรวมระบบ CRM ของคุณ</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">ยอดขายทั้งหมด</div>
                        <div class="stat-value">฿{{ number_format($totalSales, 2) }}</div>
                        <div class="stat-change {{ $salesChangePercent >= 0 ? 'positive' : '' }}">
                            {{ $salesChangePercent >= 0 ? '+' : '' }}{{ number_format($salesChangePercent, 1) }}% จากเดือนก่อน
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">จำนวนลูกค้าทั้งหมด</div>
                        <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                        <div class="stat-change {{ $newCustomersThisMonth > 0 ? 'positive' : '' }}">
                            +{{ number_format($newCustomersThisMonth) }} รายใหม่
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">พนักงานขาย</div>
                        <div class="stat-value">{{ number_format($totalSalesmen) }}</div>
                        <div class="stat-change">ทั้งหมดในระบบ</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">แคมเปญที่เปิด</div>
                        <div class="stat-value">{{ number_format($activeCampaigns) }}</div>
                        <div class="stat-change">{{ number_format($preparingCampaigns) }} กำลังเตรียม</div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>ยอดขายรายเดือน</h3>
                        <span>6 เดือนล่าสุด</span>
                    </div>
                    <div class="chart-bars" aria-label="Sales chart">
                        @foreach ($chartData as $item)
                            <div class="bar-group">
                                <div class="bar" style="height: {{ $item['height'] }}%" title="฿{{ number_format($item['total'], 2) }}"></div>
                                <label>{{ $item['label'] }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
