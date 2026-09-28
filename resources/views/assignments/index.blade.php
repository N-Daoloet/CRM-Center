<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการมอบหมายลูกค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">จัดการมอบหมายลูกค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">มอบหมายลูกค้าให้กับพนักงานขาย และติดตามสถานะล่าสุด</p>
                    </div>
                    <a href="{{ route('assignments.create') }}" class="btn">+ มอบหมายใหม่</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ลูกค้า</th>
                            <th>พนักงานขาย</th>
                            <th>วันที่มอบหมาย</th>
                            <th>สถานะ</th>
                            <th>หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            @php
                                $statusMap = [
                                    'follow_up' => ['label' => 'กำลังติดตาม', 'class' => 'active'],
                                    'pending' => ['label' => 'รอการติดต่อ', 'class' => 'pending'],
                                    'completed' => ['label' => 'เสร็จสิ้น', 'class' => 'completed'],
                                ];
                                $status = $statusMap[$assignment->status] ?? ['label' => $assignment->status, 'class' => 'pending'];
                            @endphp
                            <tr>
                                <td>{{ $assignment->customer?->name ?? '-' }}</td>
                                <td>{{ $assignment->salesman?->name ?? '-' }}</td>
                                <td>{{ $assignment->assigned_at?->format('d/m/Y') ?? '-' }}</td>
                                <td><span class="status {{ $status['class'] }}">{{ $status['label'] }}</span></td>
                                <td>{{ $assignment->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">ยังไม่มีข้อมูลมอบหมายลูกค้า</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 16px;">
                    {{ $assignments->links() }}
                </div>
            </div>
        </main>
    </div>
</body>
</html>
