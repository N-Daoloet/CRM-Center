<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขแคมเปญรายชื่อ</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .filter-tag { border:1px solid #d1d5db; background:#fff; color:#374151; padding:8px 12px; border-radius:999px; cursor:pointer; transition:all .2s ease; font-size:14px; }
        .filter-tag.active { background:#2563eb; border-color:#2563eb; color:#fff; box-shadow:0 2px 8px rgba(37,99,235,.2); }
        .edit-section { margin-top:24px; padding:20px; border:1px solid #e5e7eb; border-radius:12px; background:#fff; }
        .edit-table { width:100%; border-collapse:collapse; }
        .edit-table th, .edit-table td { padding:10px; border-bottom:1px solid #e5e7eb; text-align:left; }
        .edit-table th { background:#f9fafb; }
        @media (max-width:700px) { .edit-table-wrap { overflow-x:auto; } .edit-table { min-width:700px; } }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')
        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขแคมเปญรายชื่อ</h1>
                        <p style="margin:4px 0 0; color:#6b7280;">แก้ไขรายละเอียดและเลือกลูกค้าเป้าหมายของแคมเปญ</p>
                    </div>
                    <a href="{{ route('campaigns.index') }}" class="btn secondary">กลับหน้ารายการ</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('campaigns.updateByName', $campaign) }}">
                    @csrf
                    @method('PUT')
                    @include('campaigns.partials.edit-details', ['campaignTypeLabel' => 'แคมเปญรายชื่อ'])

                    <section class="edit-section">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:16px;">
                            <h3 style="margin:0;">ลูกค้าเป้าหมาย</h3>
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
                            </div>
                        </div>

                        <div class="edit-table-wrap">
                            <table class="edit-table">
                                <thead>
                                    <tr>
                                        <th style="width:44px;"><input type="checkbox" id="selectAllCustomers" aria-label="เลือกทั้งหมด"></th>
                                        <th>ชื่อลูกค้า</th>
                                        <th>อีเมล</th>
                                        <th>โทรศัพท์</th>
                                        <th>กลุ่ม</th>
                                    </tr>
                                </thead>
                                <tbody id="customerTableBody">
                                    @php $oldCustomerIds = old('customer_ids', $selectedCustomerIds); @endphp
                                    @forelse($customers as $customer)
                                        <tr data-customer-id="{{ $customer['id'] }}" data-is-new="{{ $customer['is_new_customer'] ? 'yes' : 'no' }}" data-total-purchase="{{ $customer['total_purchase'] }}">
                                            <td><input type="checkbox" class="customer-checkbox" name="customer_ids[]" value="{{ $customer['id'] }}" {{ in_array($customer['id'], $oldCustomerIds) ? 'checked' : '' }} aria-label="เลือกลูกค้า {{ $customer['name'] }}"></td>
                                            <td>{{ $customer['name'] }}</td>
                                            <td>{{ $customer['email'] ?: '-' }}</td>
                                            <td>{{ $customer['phone'] ?: '-' }}</td>
                                            <td>{{ $customer['is_new_customer'] ? 'ลูกค้าใหม่' : 'ลูกค้าเก่า' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" style="color:#6b7280;">ยังไม่มีลูกค้าในระบบ</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="modal-actions">
                        <a href="{{ route('campaigns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึกแคมเปญรายชื่อ</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        function selectedCustomerFilters() {
            const filters = Array.from(document.querySelectorAll('.filter-tag.active')).map(button => button.dataset.filter);
            return filters.length ? filters : ['all'];
        }

        function customerMatchesFilters(row, filters) {
            if (filters.includes('all')) return true;
            const isNew = row.dataset.isNew === 'yes';
            if (filters.includes('new') && !isNew) return false;
            if (filters.includes('existing') && isNew) return false;
            return true;
        }

        function applyCustomerFilters() {
            const rows = Array.from(document.querySelectorAll('#customerTableBody tr[data-customer-id]'));
            const filters = selectedCustomerFilters();
            rows.forEach(row => {
                row.style.display = customerMatchesFilters(row, filters) ? 'table-row' : 'none';
            });

            const visibleRows = rows.filter(row => row.style.display !== 'none');
            const selectAll = document.getElementById('selectAllCustomers');
            selectAll.checked = visibleRows.length > 0 && visibleRows.every(row => row.querySelector('.customer-checkbox').checked);
            selectAll.indeterminate = visibleRows.some(row => row.querySelector('.customer-checkbox').checked) && !selectAll.checked;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const allButton = document.querySelector('.filter-tag[data-filter="all"]');
            document.querySelectorAll('.filter-tag').forEach(button => {
                button.addEventListener('click', function () {
                    if (this.dataset.filter === 'all') {
                        document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.toggle('active', tag === this));
                    } else {
                        allButton.classList.remove('active');
                        this.classList.toggle('active');
                        if (!document.querySelector('.filter-tag.active')) allButton.classList.add('active');
                    }
                    applyCustomerFilters();
                });
            });

            document.getElementById('clearCustomerFilterBtn').addEventListener('click', function () {
                document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.toggle('active', tag === allButton));
                document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                applyCustomerFilters();
            });

            document.getElementById('selectAllCustomers').addEventListener('change', function () {
                document.querySelectorAll('#customerTableBody tr[data-customer-id]').forEach(row => {
                    if (row.style.display !== 'none') row.querySelector('.customer-checkbox').checked = this.checked;
                });
                applyCustomerFilters();
            });

            document.querySelectorAll('.customer-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', applyCustomerFilters);
            });

            document.getElementById('selectVisibleCustomersBtn').addEventListener('click', function () {
                document.querySelectorAll('#customerTableBody tr[data-customer-id]').forEach(row => {
                    if (row.style.display !== 'none') row.querySelector('.customer-checkbox').checked = true;
                });
                applyCustomerFilters();
            });

            applyCustomerFilters();
        });
    </script>
</body>
</html>