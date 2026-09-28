<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ทีมของ {{ $supervisor->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">ทีมของ {{ $supervisor->name }}</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">จัดการรายชื่อทีมและเป้าหมายยอดขายของสมาชิกแต่ละคน</p>
                    </div>
                    <a href="{{ route('salesmen.index') }}" class="btn secondary">กลับหน้าพนักงานขาย</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <div class="team-summary-grid">
                    <div class="summary-card">
                        <div class="summary-label">หัวหน้าทีม</div>
                        <div class="summary-value">{{ $supervisor->name }}</div>
                        <div class="summary-note">{{ $supervisor->position ?? 'Supervisor' }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">จำนวนทีม</div>
                        <div class="summary-value">{{ $teamMembers->count() }}</div>
                        <div class="summary-note">สมาชิกในทีมที่อยู่ภายใต้การดูแล</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">เป้าหมายรวม</div>
                        <div class="summary-value">{{ number_format($supervisor->team_sales_target) }}</div>
                        <!-- <div class="summary-value">{{ number_format($teamMembers->sum('sales_target') ?? 0, 0) }}</div> -->
                        <div class="summary-note">ยอดเป้าหมายขายทั้งหมดของทีม</div>
                    </div>
                </div>

                <div class="team-section">
                    <h3 class="section-title">เพิ่มสมาชิกลงในทีม</h3>
                    <form method="POST" action="{{ route('salesmen.team.assign', $supervisor) }}" class="modal-form">
                        @csrf
                        <div class="form-grid">
                            <label>เลือกพนักงานที่ต้องการเพิ่ม
                                <select name="member_id" required>
                                    <option value="">-- เลือกพนักงาน --</option>
                                    @foreach($availableMembers as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->position ?? 'พนักงานขาย' }})</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>ยอดขายเป้าหมาย
                                <input type="number" name="sales_target" min="0" step="10" value="0" required>
                            </label>
                        </div>
                        <div class="modal-actions">
                            <button type="submit" class="btn">เพิ่มสมาชิก</button>
                        </div>
                    </form>
                </div>

                <div class="team-section">
                    <h3 class="section-title">รายชื่อสมาชิกในทีม</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ชื่อ</th>
                                <th>ตำแหน่ง</th>
                                <th>อีเมล</th>
                                <th>เบอร์โทร</th>
                                <th>เป้าหมายยอดขาย</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamMembers as $entry)
                                <tr>
                                    <td>{{ $entry->member->name }}</td>
                                    <td>{{ $entry->member->position ?? '-' }}</td>
                                    <td>{{ $entry->member->email }}</td>
                                    <td>{{ $entry->member->phone ?? '-' }}</td>
                                    <td>{{ number_format($entry->sales_target ?? 0, 0) }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('salesmen.team.remove', $supervisor) }}" class="inline-form">
                                            @csrf
                                            <input type="hidden" name="member_id" value="{{ $entry->member->id }}">
                                            <button type="submit" class="btn secondary small">ลบ</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <!-- <tr>
                                    <td colspan="6">ยังไม่มีสมาชิกในทีม</td>
                                </tr> -->
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    @include('partials.datatables')
</body>
</html>
