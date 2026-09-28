<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard พนักงานขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <h1>Dashboard พนักงานขาย</h1>
                <p>ข้อมูลสรุปสำหรับพนักงานขายของคุณ</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">ยอดขายของฉัน</div>
                        <div class="stat-value">฿320,000</div>
                        <div class="stat-change positive">+8.2% จากเดือนก่อน</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">ลูกค้าที่รับผิดชอบ</div>
                        <div class="stat-value">24</div>
                        <div class="stat-change positive">+3 รายใหม่</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">การนัดหมายวันนี้</div>
                        <div class="stat-value">5</div>
                        <div class="stat-change">2 นัดสำเร็จ</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">อัตราสำเร็จ</div>
                        <div class="stat-value">78%</div>
                        <div class="stat-change positive">ดีขึ้นจากเดือนก่อน</div>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-header">
                        <h3>ยอดขาย 6 สัปดาห์ล่าสุด</h3>
                        <span>สัปดาห์ที่ผ่านมา</span>
                    </div>
                    <div class="chart-bars" aria-label="Sales chart for salesperson">
                        <div class="bar-group">
                            <div class="bar" style="height: 45%"></div>
                            <label>สัปดา1</label>
                        </div>
                        <div class="bar-group">
                            <div class="bar" style="height: 65%"></div>
                            <label>สัปดา2</label>
                        </div>
                        <div class="bar-group">
                            <div class="bar" style="height: 70%"></div>
                            <label>สัปดา3</label>
                        </div>
                        <div class="bar-group">
                            <div class="bar" style="height: 82%"></div>
                            <label>สัปดา4</label>
                        </div>
                        <div class="bar-group">
                            <div class="bar" style="height: 90%"></div>
                            <label>สัปดา5</label>
                        </div>
                        <div class="bar-group">
                            <div class="bar" style="height: 100%"></div>
                            <label>สัปดา6</label>
                        </div>
                    </div>
                </div>

                <div class="assigned-card">
                    <div class="chart-header">
                        <h3>ลูกค้าที่ถูกมอบหมายให้คุณ</h3>
                        <span>รายการล่าสุด</span>
                    </div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>ชื่อลูกค้า</th>
                                <th>เบอร์โทร</th>
                                <th>สถานะ</th>
                                <th>มูลค่า</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>สมชาย ใจดี</td>
                                <td>081-234-5678</td>
                                <td><span class="status active">กำลังติดตาม</span></td>
                                <td>฿45,000</td>
                            </tr>
                            <tr>
                                <td>นางสาวปิยวรรณ พันธุ์งาม</td>
                                <td>089-111-2222</td>
                                <td><span class="status pending">รอการติดต่อ</span></td>
                                <td>฿12,500</td>
                            </tr>
                            <tr>
                                <td>มานพ รักดี</td>
                                <td>086-333-4444</td>
                                <td><span class="status active">กำลังเจรจา</span></td>
                                <td>฿88,900</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
