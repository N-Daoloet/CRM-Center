<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การตั้งค่า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <h1>การตั้งค่า</h1>
                <p>ตั้งค่าระบบและข้อมูลทั่วไปของ CRM</p>

                @if (session('success'))
                    <p class="status active">{{ session('success') }}</p>
                @endif

                <div class="settings-grid">
                    <div class="settings-card">
                        <h3>กำหนด Target รายเดือน</h3>
                        <p>ตั้งค่าเป้าหมายยอดขายสำหรับแต่ละเดือน</p>
                        <form class="modal-form">
                            <label>เดือน</label>
                            <select>
                                <option>สิงหาคม 2026</option>
                                <option>กันยายน 2026</option>
                                <option>ตุลาคม 2026</option>
                            </select>

                            <label>Target ยอดขายรวม</label>
                            <input type="text" value="฿2,500,000">

                            <label>Target ลูกค้าใหม่</label>
                            <input type="text" value="40 คน">

                            <div class="modal-actions">
                                <button type="submit" class="btn">บันทึก</button>
                            </div>
                        </form>
                    </div>

                    <div class="settings-card">
                        <h3>Assign Target รายทีม Supervisor</h3>
                        <p>กำหนดเป้าหมายยอดขายให้แต่ละทีมจากข้อมูลจริงในระบบ</p>
                        <table class="mini-table">
                            <thead>
                                <tr>
                                    <th>Supervisor</th>
                                    <th>จำนวนสมาชิกทีม</th>
                                    <th>Target ทีม</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supervisorTeams as $supervisor)
                                    <tr>
                                        <td>{{ $supervisor->name }}</td>
                                        <td>{{ $supervisor->setup_teams_as_supervisor_count }} คน</td>
                                        <td>
                                            <form action="{{ route('settings.supervisors.team-target', $supervisor) }}" method="POST" class="modal-form" style="margin: 0;">
                                                @csrf
                                                <input
                                                    type="number"
                                                    name="team_sales_target"
                                                    min="0"
                                                    step="0.01"
                                                    value="{{ old('team_sales_target', $supervisor->team_sales_target ?? 0) }}"
                                                    placeholder="เช่น 500000"
                                                    required
                                                >
                                        </td>
                                        <td>
                                                <button type="submit" class="btn">บันทึก</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">ยังไม่พบทีม Supervisor ในระบบ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
