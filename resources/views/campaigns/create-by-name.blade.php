<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างแคมเปญรายชื่อ</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .filter-tag {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
            padding: 8px 12px;
            border-radius: 999px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .filter-tag.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">สร้างแคมเปญรายชื่อ</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ระบุกลุ่มลูกค้าเป้าหมายและจัดสรรกลุ่มที่ต้องการส่งแคมเปญได้</p>
                    </div>
                    <a href="{{ route('campaigns.index') }}" class="btn secondary">กลับหน้ารายการ</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('campaigns.storeByName') }}">
                    @csrf

                    <div class="form-grid">
                        <label class="full-span">ชื่อแคมเปญรายชื่อ
                            <input type="text" name="name" placeholder="เช่น แคมเปญลูกค้าชั้นนำ" required>
                        </label>

                        <label>ประเภทแคมเปญ
                            <input type="text" value="แคมเปญรายชื่อ" readonly>
                        </label>

                        <label>วันที่เริ่มต้น
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required>
                        </label>

                        <label>วันที่สิ้นสุด
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                        </label>

                        <label class="full-span">รายละเอียดแคมเปญ
                            <textarea rows="4" name="description" placeholder="ระบุรายละเอียดหรือเงื่อนไขของแคมเปญ"></textarea>
                        </label>
                    </div>

                    <div style="margin-top: 24px; border:1px solid #e5e7eb; border-radius:12px; padding:20px; background:#f9fafb;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                            <h3 style="margin:0;">เลือกลูกค้าร่วมแคมเปญ</h3>
                            <button type="button" class="btn secondary" id="selectVisibleCustomersBtn">เลือกทั้งหมดในหน้าจอ</button>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                <span style="font-weight:600;">กรองกลุ่มลูกค้า</span>
                                <button type="button" class="btn secondary" id="clearCustomerFilterBtn">ล้างค่า filter</button>
                            </div>

                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                <button type="button" class="filter-tag active" data-filter="all">ทั้งหมด</button>
                                <button type="button" class="filter-tag" data-filter="new">ลูกค้าใหม่</button>
                                <button type="button" class="filter-tag" data-filter="existing">ลูกค้าเก่า</button>
                                <!-- <button type="button" class="filter-tag" data-filter="high-value">มียอดซื้อ > 10,000</button> -->
                            </div>
                        </div>

                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:10px; width:40px;">
                                        <input type="checkbox" id="selectAllCustomers" aria-label="เลือกทั้งหมด">
                                    </th>
                                    <th style="text-align:left; padding:10px;">ลูกค้า</th>
                                    <th style="text-align:left; padding:10px;">อีเมล</th>
                                    <th style="text-align:left; padding:10px;">โทรศัพท์</th>
                                    <!-- <th style="text-align:left; padding:10px;">ยอดซื้อ</th> -->
                                    <th style="text-align:left; padding:10px;">กลุ่ม</th>
                                </tr>
                            </thead>
                            <tbody id="customerTableBody">
                                @foreach($customers as $customer)
                                    <tr data-customer-id="{{ $customer['id'] }}"
                                        data-is-new="{{ $customer['is_new_customer'] ? 'yes' : 'no' }}"
                                        data-total-purchase="{{ (float) ($customer['total_purchase'] ?? 0) }}"
                                        style="display:table-row;">
                                        <td style="padding:10px;">
                                            <input type="checkbox" class="customer-checkbox" value="{{ $customer['id'] }}" data-customer-id="{{ $customer['id'] }}">
                                        </td>
                                        <td style="padding:10px;">{{ $customer['name'] }}</td>
                                        <td style="padding:10px;">{{ $customer['email'] ?: '-' }}</td>
                                        <td style="padding:10px;">{{ $customer['phone'] ?: '-' }}</td>
                                        <!-- <td style="padding:10px;">{{ number_format((float) ($customer['total_purchase'] ?? 0), 2) }}</td> -->
                                        <td style="padding:10px;">{{ $customer['is_new_customer'] ? 'ลูกค้าใหม่' : 'ลูกค้าเก่า' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('campaigns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function getSelectedFilterValues() {
            const selected = Array.from(document.querySelectorAll('.filter-tag.active')).map(tag => tag.dataset.filter);
            return selected.length ? selected : ['all'];
        }

        function matchesCustomerFilter(row, filters) {
            const isNewCustomer = row.dataset.isNew === 'yes';
            const totalPurchase = Number(row.dataset.totalPurchase || 0);

            if (filters.includes('all')) {
                return true;
            }

            if (filters.includes('new') && !isNewCustomer) {
                return false;
            }

            if (filters.includes('existing') && isNewCustomer) {
                return false;
            }

            if (filters.includes('high-value') && totalPurchase <= 10000) {
                return false;
            }

            return true;
        }

        function applyCustomerFilters() {
            const filters = getSelectedFilterValues();
            const rows = document.querySelectorAll('#customerTableBody tr[data-customer-id]');

            rows.forEach((row) => {
                const visible = matchesCustomerFilter(row, filters);
                row.style.display = visible ? 'table-row' : 'none';
            });

            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
            const allChecked = visibleRows.length > 0 && visibleRows.every(row => {
                const checkbox = row.querySelector('.customer-checkbox');
                return checkbox && checkbox.checked;
            });

            const selectAllCheckbox = document.getElementById('selectAllCustomers');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
            }
        }

        function syncSelectedCustomers() {
            const existingInputs = document.querySelectorAll('input[name^="customers["]');
            existingInputs.forEach(input => input.remove());

            const checkedRows = document.querySelectorAll('.customer-checkbox:checked');
            checkedRows.forEach((checkbox, index) => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `customers[${index}][customer_id]`;
                hiddenInput.value = checkbox.value;
                document.querySelector('form.modal-form').appendChild(hiddenInput);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const clearCustomerFilterBtn = document.getElementById('clearCustomerFilterBtn');
            const selectAllCheckbox = document.getElementById('selectAllCustomers');
            const selectVisibleBtn = document.getElementById('selectVisibleCustomersBtn');

            document.querySelectorAll('.filter-tag').forEach((button) => {
                button.addEventListener('click', function () {
                    const filterValue = this.dataset.filter;

                    if (filterValue === 'all') {
                        document.querySelectorAll('.filter-tag').forEach(tag => {
                            tag.classList.toggle('active', tag.dataset.filter === 'all');
                        });
                    } else {
                        const allBtn = document.querySelector('.filter-tag[data-filter="all"]');
                        allBtn.classList.remove('active');

                        this.classList.toggle('active');

                        const activeFilters = Array.from(document.querySelectorAll('.filter-tag.active')).map(tag => tag.dataset.filter);
                        if (!activeFilters.length) {
                            allBtn.classList.add('active');
                        }
                    }

                    applyCustomerFilters();
                });
            });

            clearCustomerFilterBtn.addEventListener('click', function () {
                document.querySelectorAll('.filter-tag').forEach(tag => {
                    tag.classList.toggle('active', tag.dataset.filter === 'all');
                });

                document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                syncSelectedCustomers();

                applyCustomerFilters();
            });

            selectAllCheckbox.addEventListener('change', function () {
                const visibleRows = Array.from(document.querySelectorAll('#customerTableBody tr[data-customer-id]')).filter(row => row.style.display !== 'none');
                visibleRows.forEach((row) => {
                    const checkbox = row.querySelector('.customer-checkbox');
                    if (checkbox) {
                        checkbox.checked = this.checked;
                    }
                });

                syncSelectedCustomers();
            });

            document.querySelectorAll('.customer-checkbox').forEach((checkbox) => {
                checkbox.addEventListener('change', function () {
                    const totalVisible = document.querySelectorAll('#customerTableBody tr[data-customer-id]').length;
                    const visibleRows = Array.from(document.querySelectorAll('#customerTableBody tr[data-customer-id]')).filter(row => row.style.display !== 'none');
                    const allVisibleChecked = visibleRows.length > 0 && visibleRows.every(row => row.querySelector('.customer-checkbox').checked);

                    selectAllCheckbox.checked = allVisibleChecked && visibleRows.length === totalVisible;
                    syncSelectedCustomers();
                });
            });

            selectVisibleBtn.addEventListener('click', function () {
                const visibleRows = Array.from(document.querySelectorAll('#customerTableBody tr[data-customer-id]')).filter(row => row.style.display !== 'none');
                visibleRows.forEach((row) => {
                    const checkbox = row.querySelector('.customer-checkbox');
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                syncSelectedCustomers();
                applyCustomerFilters();
            });

            applyCustomerFilters();
            syncSelectedCustomers();
        });
    </script>
</body>
</html>
