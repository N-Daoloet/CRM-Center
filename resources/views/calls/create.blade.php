<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มการโทร</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">เพิ่มการโทร</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">บันทึกการติดต่อใหม่ในระบบ</p>
                    </div>
                    <a href="{{ route('calls.index') }}" class="btn secondary">กลับหน้าประวัติการโทร</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('calls.store') }}">
                    @csrf

                    <label>ลูกค้า</label>
                    <select name="customer_id" required>
                        <option value="">-- เลือกลูกค้า --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>

                    <label>พนักงานขาย</label>
                    <select name="salesman_id" required>
                        <option value="">-- เลือกพนักงานขาย --</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}">{{ $salesman->name }}</option>
                        @endforeach
                    </select>

                    <label>แคมเปญ</label>
                    <select name="campaign_id" required>
                        <option value="">-- เลือกแคมเปญ --</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </select>

                    <label>วันที่และเวลา</label>
                    <input type="datetime-local" name="call_time" value="{{ now()->format('Y-m-d\TH:i') }}" required>

                    <label>ผลลัพธ์</label>
                    <input type="text" name="result" placeholder="เช่น นัดหมายต่อ" required>

                    <label>โน้ต</label>
                    <textarea name="notes" rows="4"></textarea>

                    <div class="modal-actions">
                        <a href="{{ route('calls.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
