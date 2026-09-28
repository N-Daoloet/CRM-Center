<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการโทร</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">ประวัติการโทร</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ดูรายการการโทรทั้งหมดจากฐานข้อมูล</p>
                    </div>
                    <a href="{{ route('calls.create') }}" class="btn">+ เพิ่มการโทร</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <!-- <div class="list-toolbar">
                    <div class="list-title-block">
                        <h3 style="margin:0;">ประวัติการโทร</h3>
                        <p style="margin:4px 0 0; color:#6b7280;">เรียกดูข้อมูลการโทรในตารางที่รองรับการค้นหาและเรียงลำดับ</p>
                    </div>
                    <a href="{{ route('calls.create') }}" class="btn">+ เพิ่มการโทร</a>
                </div> -->

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ลูกค้า</th>
                            <th>พนักงานขาย</th>
                            <!-- <th>แคมเปญ</th> -->
                            <th>เวลา</th>
                            <th>ผลลัพธ์</th>
                            <th>โน้ต</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calls as $call)
                            <tr>
                                <td>{{ $call->id }}</td>
                                <td>{{ $call->customer->name ?? '-' }}</td>
                                <td>{{ $call->salesman->name ?? '-' }}</td>
                                <!-- <td>{{ $call->campaign->name ?? '-' }}</td> -->
                                <td>{{ $call->call_time }}</td>
                                <td>{{ $call->result }}</td>
                                <td>{{ Str::limit($call->notes ?? '-', 40) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">ยังไม่มีข้อมูลการโทร</td>
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
