<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แคมเปญ</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แคมเปญ</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">จัดการแคมเปญในระบบ</p>
                    </div>
                   <div>
                        <a href="{{ route('campaigns.create') }}" class="btn info">+ เพิ่มโปรโมชั่นสินค้า</a>
                        
                        <a href="{{ route('campaigns.createByName') }}" class="btn">+ เพิ่มแคมเปญรายชื่อ</a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin: 20px 0 24px;">
                    <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border:1px solid #bfdbfe; border-radius:14px; padding:18px;">
                        <div style="font-size:12px; color:#1d4ed8; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">รวมแคมเปญ</div>
                        <div style="font-size:28px; font-weight:700; margin-top:8px; color:#1e3a8a;">{{ $summary['total'] }}</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border:1px solid #a7f3d0; border-radius:14px; padding:18px;">
                        <div style="font-size:12px; color:#047857; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">กำลังใช้งาน</div>
                        <div style="font-size:28px; font-weight:700; margin-top:8px; color:#065f46;">{{ $summary['active'] }}</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #fff7ed, #ffedd5); border:1px solid #fdba74; border-radius:14px; padding:18px;">
                        <div style="font-size:12px; color:#c2410c; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">วางแผน</div>
                        <div style="font-size:28px; font-weight:700; margin-top:8px; color:#9a4d11;">{{ $summary['planned'] }}</div>
                    </div>
                    <div style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border:1px solid #d1d5db; border-radius:14px; padding:18px;">
                        <div style="font-size:12px; color:#374151; font-weight:600; text-transform:uppercase; letter-spacing:0.05em;">เสร็จสิ้น</div>
                        <div style="font-size:28px; font-weight:700; margin-top:8px; color:#111827;">{{ $summary['completed'] }}</div>
                    </div>
                </div>

                <div style="margin-bottom: 18px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                    <div>
                        <h3 style="margin:0;">รายการแคมเปญ</h3>
                    </div>
                </div>

                <!-- <form method="GET" action="{{ route('campaigns.index') }}" style="display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; align-items:end;">
                    <div>
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">ค้นหา</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="ชื่อ/รายละเอียด/สถานะ" style="min-width:220px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">สถานะ</label>
                        <select name="status" style="min-width:160px;">
                            <option value="">ทั้งหมด</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>active</option>
                            <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>planned</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>completed</option>
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">ประเภทแคมเปญ</label>
                        <select name="campaign_type" style="min-width:180px;">
                            <option value="">ทั้งหมด</option>
                            @foreach($campaignTypes as $type)
                                <option value="{{ $type }}" {{ request('campaign_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn">ค้นหา</button>
                    <a href="{{ route('campaigns.index') }}" class="btn secondary">ล้าง</a>
                </form> -->

                <table class="data-table" data-order-column="0" data-order-direction="desc">
                    <thead>
                        <tr>
                            <!-- <th>ไอดี</th> -->
                            <th style="text-align:left;">ชื่อแคมเปญ</th>
                            <th>ประเภทแคมเปญ</th>
                            <th>รายละเอียด</th>
                            <!-- <th>สินค้า</th> -->
                            <th>ช่วงเวลา</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td data-order="{{ $campaign->id }}" style="text-align:left;">
                                    <div style="font-weight:700;">{{ $campaign->name }}</div>
                                </td>
                                <td>
                                    @php
                                        $isProductPromotion = $campaign->products_count > 0 || $campaign->campaign_type === 'โปรโมชั่นสินค้า';
                                    @endphp
                                    {{ $isProductPromotion ? 'โปรโมชั่นสินค้า' : 'แคมเปญรายชื่อ' }}
                                </td>
                                <td>{{ Str::limit($campaign->description ?? '-', 40) }}</td>
                                <td>
                                    <div>{{ $campaign->start_date }}</div>
                                    <small style="color:#6b7280;">ถึง {{ $campaign->end_date }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => ['background' => '#dcfce7', 'color' => '#166534'],
                                            'planned' => ['background' => '#fef3c7', 'color' => '#92400e'],
                                            'completed' => ['background' => '#e5e7eb', 'color' => '#374151'],
                                        ];
                                        $statusStyle = $statusColors[$campaign->status] ?? ['background' => '#e0e7ff', 'color' => '#3730a3'];
                                    @endphp
                                    <span style="display:inline-block; padding:5px 10px; border-radius:999px; font-size:12px; font-weight:600; background:{{ $statusStyle['background'] }}; color:{{ $statusStyle['color'] }};">
                                        {{ $campaign->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('campaigns.edit', $campaign) }}" class="btn secondary">แก้ไข</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center;">ยังไม่มีข้อมูลแคมเปญ</td>
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
