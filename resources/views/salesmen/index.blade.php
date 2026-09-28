<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พนักงานขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">พนักงานขาย</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">จัดการข้อมูลทีมขายในระบบ</p>
                    </div>
                    <a href="{{ route('users.create') }}" class="btn">+ เพิ่มพนักงานขาย</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <div class="team-summary-grid">
                    <div class="summary-card">
                        <div class="summary-label">ทีมผู้ดูแล</div>
                        <div class="summary-value">{{ $totalTeams }}</div>
                        <div class="summary-note">จำนวน supervisor ที่มีทีมอยู่</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">พนักงานขาย</div>
                        <div class="summary-value">{{ $totalMembers }}</div>
                        <div class="summary-note">จำนวนพนักงานในระบบทั้งหมด</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">ยังไม่ได้ผูกทีม</div>
                        <div class="summary-value">{{ $unassignedMembers }}</div>
                        <div class="summary-note">พนักงานที่ยังไม่มี supervisor</div>
                    </div>
                </div>

                <div class="team-section">
                    <h3 class="section-title">ภาพรวมทีมตาม supervisor</h3>
                    <div class="team-grid">
                        @forelse($supervisors as $supervisor)
                            <a href="{{ route('salesmen.team.detail', $supervisor->id) }}" class="team-card" style="text-decoration: none; color: inherit; display: block;">
                                <div class="team-card-head">
                                    <h3>{{ $supervisor->name }}</h3>
                                    <span class="status active">Supervisor</span>
                                </div>
                                <p class="team-focus">{{ $supervisor->email }}</p>
                                <p class="team-focus">จำนวนสมาชิก {{ count($teamMembersBySupervisor[$supervisor->id] ?? []) ?: '0' }} คน</p>
                            </a>
                        @empty
                            <div class="team-card">
                                <p class="team-focus">ยังไม่มี supervisor ในระบบ</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- <div class="list-toolbar">
                    <div class="list-title-block">
                        <h3 style="margin:0;">พนักงานขาย</h3>
                        <p style="margin:4px 0 0; color:#6b7280;">เรียกดูข้อมูลทีมขายในตารางที่รองรับการเรียงลำดับ</p>
                    </div>
                    <a href="{{ route('salesmen.create') }}" class="btn">+ เพิ่มพนักงานขาย</a>
                </div> -->

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อ</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesmen as $salesman)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $salesman->name }}</td>
                                <td>{{ $salesman->email }}</td>
                                <td>พนักงานขาย</td>
                                <td><span class="status active">Active</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">ยังไม่มีข้อมูลพนักงานขาย</td>
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
