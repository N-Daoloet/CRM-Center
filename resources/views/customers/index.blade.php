<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลูกค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">รายชื่อลูกค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ข้อมูลลูกค้าในระบบ CRM</p>
                    </div>
                    <a href="{{ route('customers.create') }}" class="btn">+ เพิ่มลูกค้า</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <!-- <div class="list-toolbar">
                    <div class="list-title-block">
                        <h3 style="margin:0;">รายการลูกค้า</h3>
                        <p style="margin:4px 0 0; color:#6b7280;">ดูข้อมูลลูกค้าทั้งหมดในตารางที่รองรับการค้นหาและเรียงลำดับ</p>
                    </div>
                    <a href="{{ route('customers.create') }}" class="btn">+ เพิ่มลูกค้า</a>
                </div> -->

                @php
                    $salesmen = \App\Models\User::query()->where('role', 'sale')->get();
                    $campaigns = \App\Models\Campaign::all();
                @endphp

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อ</th>
                            <th>อีเมล</th>
                            <th>เบอร์โทร</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? '-' }}</td>
                                <td>
                                    @php
                                        $status = $customer->status ?? 'active';
                                        $statusLabel = $status === 'inactive' ? 'Inactive' : 'Active';
                                        $statusClass = $status === 'inactive' ? 'status-badge inactive' : 'status-badge active';
                                    @endphp
                                    <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td><div class="action-buttons">
                                    <button type="button" class="btn print-btn" data-customer-id="{{ $customer->id }}" data-customer-name="{{ $customer->name }}" onclick="openCallModal(this)">บันทึกการโทร</button>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn secondary">แก้ไข</a>
                                </div></td>
                            </tr>
                        @empty
                            <!-- <tr>
                                <td colspan="6">ยังไม่มีข้อมูลลูกค้า</td>
                            </tr> -->
                        @endforelse
                    </tbody>
                </table>

            </div>
        </main>
    </div>

    <div id="callModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header">
                <h3 style="margin:0;">บันทึกการโทร</h3>
                <button type="button" class="close-btn" onclick="closeCallModal()">×</button>
            </div>

            <form class="modal-form" method="POST" action="{{ route('calls.store') }}">
                @csrf
                <input type="hidden" name="customer_id" id="callCustomerId">

                <label>ลูกค้า
                    <input type="text" id="callCustomerName" readonly>
                </label>

                <label>พนักงานขาย
                    <select name="salesman_id" required>
                        <option value="">-- เลือกพนักงานขาย --</option>
                        @foreach($salesmen as $salesman)
                            <option value="{{ $salesman->id }}">{{ $salesman->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>แคมเปญ
                    <select name="campaign_id">
                        <option value="">-- ไม่ใช้แคมเปญ --</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>วันที่และเวลา
                    <input type="datetime-local" name="call_time" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </label>

                <label>ผลลัพธ์
                    <input type="text" name="result" placeholder="เช่น นัดหมายต่อ / ตอบรับ / ไม่ตอบ" required>
                </label>

                <label>Next Step
                    <input type="text" name="next_step" placeholder="เช่น ส่งโปรโมชัน / นัดหมายวันถัดไป / วางแผนต่อ" required>
                </label>

                <label>โน้ต
                    <textarea name="notes" rows="4" placeholder="รายละเอียดการสนทนา"></textarea>
                </label>

                <div class="modal-actions">
                    <button type="button" class="btn secondary" onclick="closeCallModal()">ยกเลิก</button>
                    <button type="submit" class="btn">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCallModal(button) {
            const modal = document.getElementById('callModal');
            const customerId = button.dataset.customerId;
            const customerName = button.dataset.customerName;
            const customerIdInput = document.getElementById('callCustomerId');
            const customerNameInput = document.getElementById('callCustomerName');

            if (!modal || !customerIdInput || !customerNameInput) return;

            customerIdInput.value = customerId;
            customerNameInput.value = customerName;
            modal.style.display = 'flex';
        }

        function closeCallModal() {
            const modal = document.getElementById('callModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('click', function (event) {
            const modal = document.getElementById('callModal');
            if (event.target === modal) {
                closeCallModal();
            }
        });
    </script>

    @include('partials.datatables')
</body>
</html>
