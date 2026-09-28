<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างการมอบหมายลูกค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .customer-picker {
            background: linear-gradient(135deg, #fffaf5 0%, #ffffff 100%);
            border: 1px solid #f3e2d3;
            border-radius: 14px;
            padding: 14px;
            max-width: 680px;
            width: 100%;
            margin: 0 auto;
        }

        .picker-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .picker-caption {
            font-size: 13px;
            color: #6b7280;
        }

        .picker-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .picker-btn {
            border: 1px solid #fdba74;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .picker-btn:hover {
            background: #ffedd5;
            border-color: #fb923c;
        }

        .picker-count {
            margin-top: 10px;
            font-size: 12px;
            color: #7c2d12;
            font-weight: 600;
        }

        .customer-filter {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 10px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .customer-filter:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
        }

        .customer-group-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 10px;
        }

        .customer-filter-btn {
            padding: 6px 11px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #fff;
            color: #4b5563;
            font: inherit;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .customer-filter-btn:hover,
        .customer-filter-btn.active {
            border-color: #fb923c;
            background: #fff7ed;
            color: #9a3412;
            font-weight: 700;
        }

        .customer-list {
            display: grid;
            gap: 7px;
            max-height: 320px;
            overflow-y: auto;
            padding: 2px 4px 2px 2px;
        }

        .customer-list-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid #f3e2d3;
            border-radius: 10px;
            background: #fff;
            color: #374151;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .customer-list-item[hidden] {
            display: none;
        }

        .customer-list-item:hover,
        .customer-list-item:has(input:checked) {
            border-color: #fb923c;
            background: #fff7ed;
        }

        .customer-list-item input {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: #ea580c;
            flex: 0 0 auto;
        }

        .customer-list-name {
            font-weight: 600;
        }

        .customer-list-phone {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 12px;
            font-weight: 400;
        }

        .customer-list-empty {
            display: none;
            padding: 16px;
            border: 1px dashed #fdba74;
            border-radius: 10px;
            color: #9a3412;
            text-align: center;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .customer-picker {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card form-card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">สร้างการมอบหมายลูกค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">เลือกลูกค้าหลายคนเพื่อมอบหมายให้พนักงานขายคนเดียว</p>
                    </div>
                    <a href="{{ route('assignments.index') }}" class="btn secondary">กลับหน้ารายการ</a>
                </div>

                @if($errors->any())
                    <div class="alert error">
                        <ul style="margin: 0; padding-left: 18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($salesmen->isEmpty())
                    <div class="alert warning">ยังไม่มีพนักงานขายในระบบ กรุณาเพิ่มพนักงานขายก่อนทำการมอบหมาย</div>
                @endif

                @if($customers->isEmpty())
                    <div class="alert warning">ยังไม่มีลูกค้าในระบบ กรุณาเพิ่มลูกค้าก่อนทำการมอบหมาย</div>
                @endif

                <form method="POST" action="{{ route('assignments.store') }}" class="modal-form">
                    @csrf

                    <div class="form-section">
                        <h3>ข้อมูลการมอบหมาย</h3>
                        <div class="form-grid">
                            <label>
                                เลือกพนักงานขาย
                                <select name="salesman_id" required {{ $salesmen->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">-- กรุณาเลือก --</option>
                                    @foreach($salesmen as $salesman)
                                        <option value="{{ $salesman->id }}" @selected(old('salesman_id') == $salesman->id)>
                                            {{ $salesman->name }} ({{ $salesman->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label>
                                วันที่มอบหมาย
                                <input type="date" name="assigned_at" value="{{ old('assigned_at', now()->toDateString()) }}" required>
                            </label>

                            <!-- <label class="full-span">
                                สถานะ
                                <select name="status" required>
                                    <option value="follow_up" @selected(old('status', 'follow_up') === 'follow_up')>กำลังติดตาม</option>
                                    <option value="pending" @selected(old('status') === 'pending')>รอการติดต่อ</option>
                                    <option value="completed" @selected(old('status') === 'completed')>เสร็จสิ้น</option>
                                </select>
                            </label> -->

                            <label class="full-span">
                                หมายเหตุ
                                <textarea name="notes" rows="3" placeholder="เช่น กลุ่มลูกค้าจากแคมเปญ A">{{ old('notes') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>เลือกลูกค้า (เลือกได้หลายคน)</h3>
                        <div class="customer-picker">
                            <div class="picker-toolbar">
                                <span class="picker-caption">ค้นหาและเลือกได้หลายรายการ</span>
                                <div class="picker-actions">
                                    <button type="button" id="select-all-customers" class="picker-btn" {{ $customers->isEmpty() || $salesmen->isEmpty() ? 'disabled' : '' }}>เลือกทั้งหมด</button>
                                    <button type="button" id="clear-all-customers" class="picker-btn" {{ $customers->isEmpty() || $salesmen->isEmpty() ? 'disabled' : '' }}>ล้างทั้งหมด</button>
                                </div>
                            </div>

                            <input
                                type="search"
                                id="customer-filter"
                                class="customer-filter"
                                placeholder="ค้นหาชื่อลูกค้าหรือเบอร์โทรศัพท์..."
                                aria-label="ค้นหาลูกค้า"
                                {{ $customers->isEmpty() || $salesmen->isEmpty() ? 'disabled' : '' }}
                            >

                            <div class="customer-group-filters" role="group" aria-label="กรองตามประเภทลูกค้า">
                                <button type="button" class="customer-filter-btn active" data-customer-group="all">ทั้งหมด</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="1">ประจำ</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="2">ส่วนตัว</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="3">ลูกค้าใหม่</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="4">ลูกค้าถังกลาง</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="5">เคยเป็น</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="6">ติดตาม</button>
                                <button type="button" class="customer-filter-btn" data-customer-group="none">ไม่ระบุ</button>
                            </div>

                            @php
                                $selectedCustomerIds = array_map('intval', old('customer_ids', []));
                            @endphp
                            <div class="customer-list" id="customer-list">
                                @foreach($customers as $customer)
                                    <label
                                        class="customer-list-item"
                                        data-search="{{ strtolower($customer->name . ' ' . ($customer->phone ?? '')) }}"
                                        data-customer-group="{{ $customer->flag ?? 'none' }}"
                                    >
                                        <input
                                            type="checkbox"
                                            name="customer_ids[]"
                                            value="{{ $customer->id }}"
                                            @checked(in_array($customer->id, $selectedCustomerIds, true))
                                            {{ $customers->isEmpty() || $salesmen->isEmpty() ? 'disabled' : '' }}
                                        >
                                        <span>
                                            <span class="customer-list-name">{{ $customer->name }}</span>
                                            @if($customer->phone)
                                                <span class="customer-list-phone">{{ $customer->phone }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="customer-list-empty" id="customer-list-empty">ไม่พบลูกค้าที่ตรงกับคำค้นหา</div>

                            <div class="picker-count" id="customer-selected-count">เลือกแล้ว {{ count(old('customer_ids', [])) }} รายการ</div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('assignments.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn" {{ $customers->isEmpty() || $salesmen->isEmpty() ? 'disabled' : '' }}>บันทึกการมอบหมาย</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const customerList = document.getElementById('customer-list');
            const customerFilter = document.getElementById('customer-filter');
            const customerCheckboxes = Array.from(document.querySelectorAll('input[name="customer_ids[]"]'));
            const selectedCount = document.getElementById('customer-selected-count');
            const emptyMessage = document.getElementById('customer-list-empty');
            const groupFilterButtons = Array.from(document.querySelectorAll('.customer-filter-btn'));
            let activeCustomerGroup = 'all';

            function updateSelectedCount() {
                const count = customerCheckboxes.filter((checkbox) => checkbox.checked).length;
                selectedCount.textContent = `เลือกแล้ว ${count} รายการ`;
            }

            function filterCustomers() {
                const query = customerFilter.value.trim().toLocaleLowerCase();
                let visibleCount = 0;

                customerList.querySelectorAll('.customer-list-item').forEach((item) => {
                    const matchesSearch = item.dataset.search.includes(query);
                    const matchesGroup = activeCustomerGroup === 'all'
                        || item.dataset.customerGroup === activeCustomerGroup;
                    const matches = matchesSearch && matchesGroup;
                    item.hidden = !matches;
                    if (matches) visibleCount++;
                });

                emptyMessage.style.display = visibleCount === 0 ? 'block' : 'none';
            }

            groupFilterButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    activeCustomerGroup = this.dataset.customerGroup;
                    groupFilterButtons.forEach((filterButton) => {
                        filterButton.classList.toggle('active', filterButton === this);
                    });
                    filterCustomers();
                });
            });

            document.getElementById('select-all-customers').addEventListener('click', function () {
                if (this.disabled) return;

                customerCheckboxes.forEach((checkbox) => {
                    if (!checkbox.disabled) checkbox.checked = true;
                });
                updateSelectedCount();
            });

            document.getElementById('clear-all-customers').addEventListener('click', function () {
                if (this.disabled) return;

                customerCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                customerFilter.value = '';
                activeCustomerGroup = 'all';
                groupFilterButtons.forEach((filterButton) => {
                    filterButton.classList.toggle('active', filterButton.dataset.customerGroup === 'all');
                });

                filterCustomers();
                updateSelectedCount();
            });

            customerCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            customerFilter.addEventListener('input', filterCustomers);
            filterCustomers();
            updateSelectedCount();
        });
    </script>
</body>
</html>
