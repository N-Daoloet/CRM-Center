<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">การขาย</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">รายการคำสั่งซื้อและการขายในระบบ</p>
                    </div>
                    <!-- <a href="{{ route('sales.overview') }}" class="btn">ภาพรวมการขาย</a> -->
                    <a href="{{ route('sales.orders.create') }}" class="btn">+ เพิ่มรายการขาย</a>
                </div>

                @if(session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <table id="sales-orders-table" class="data-table" data-server-side="true">
                    <thead>
                        <tr>
                            <th>เลขออเดอร์</th>
                            <th>ลูกค้า</th>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ยอดเงิน</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </main>
    </div>
    @include('partials.datatables')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('sales-orders-table');
            if (!table || table.dataset.dtInitialized) {
                return;
            }

            new DataTable(table, {
                processing: true,
                serverSide: true,
                searching: true,
                paging: true,
                ordering: true,
                scrollX :true,
                info: false,
                lengthChange: true,
                pageLength: 10,
                ajax: {
                    url: '{{ route('sales.orders.data') }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'order_no', name: 'order_no' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'product_name', name: 'product_name' },
                    { data: 'quantity', name: 'quantity' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'status', name: 'status' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            table.dataset.dtInitialized = 'true';
        });
    </script>
</body>
</html>
