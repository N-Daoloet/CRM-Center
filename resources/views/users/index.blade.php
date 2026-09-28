<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <style>
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal-card {
            width: min(100%, 640px);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            padding: 20px;
        }

        .modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .modal-title {
            margin: 0;
            font-size: 20px;
        }

        .btn-icon {
            border: 0;
            background: transparent;
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
            color: #64748b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        .form-group label {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-actions {
            margin-top: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-secondary {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
        }

        .status-message {
            margin: 0 0 14px;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: span 1;
            }
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
                        <h1 style="margin:0;">จัดการผู้ใช้</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">จัดการสิทธิ์และบัญชีผู้ใช้งานในระบบ</p>
                    </div>
                    <a href="{{ route('users.create') }}" class="btn">+ เพิ่มผู้ใช้</a>
                    <!-- <button type="button" id="openCreateUserModal" class="btn">+ เพิ่มผู้ใช้</button> -->
                </div>

                @if (session('status'))
                    <p class="status-message status-success">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="status-message status-error">
                        <strong>ไม่สามารถบันทึกข้อมูลได้</strong>
                        <ul style="margin:8px 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>รหัสพนักงาน</th>
                            <th>ชื่อ</th>
                            <th>อีเมล</th>
                            <th>บทบาท</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->employee_code ?? '-' }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')
                                            Admin
                                            @break
                                        @case('supervisor')
                                            Supervisor
                                            @break
                                        @case('owner')
                                            Owner
                                            @break
                                        @default
                                            พนักงานขาย
                                    @endswitch
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="status active">Active</span>
                                    @else
                                        <span class="status pending">Pending</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal-backdrop" id="createUserModal">
        <div class="modal-card">
            <div class="modal-head">
                <h2 class="modal-title">เพิ่มผู้ใช้ใหม่</h2>
                <button type="button" class="btn-icon" id="closeCreateUserModal">&times;</button>
            </div>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label>รหัสพนักงาน </label>
                        <input type="text" value="{{ $nextEmployeeCode }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="name">ชื่อ-นามสกุล</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="position">ตำแหน่ง</label>
                        <input id="position" name="position" type="text" value="{{ old('position') }}">
                    </div>

                    <div class="form-group">
                        <label for="role">บทบาท</label>
                        <select id="role" name="role" required>
                            <option value="sale" {{ old('role') === 'sale' ? 'selected' : '' }}>พนักงานขาย</option>
                            <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="owner" {{ old('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="password">รหัสผ่าน</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">ยืนยันรหัสผ่าน</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelCreateUserModal">ยกเลิก</button>
                    <button type="submit" class="btn">บันทึกผู้ใช้</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(function () {
            $('#usersTable').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                language: {
                    search: 'ค้นหา:',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        next: 'ถัดไป'
                    },
                    zeroRecords: 'ไม่พบข้อมูล',
                    infoEmpty: 'ไม่มีข้อมูล',
                }
            });

            const modal = document.getElementById('createUserModal');
            const openBtn = document.getElementById('openCreateUserModal');
            const closeBtn = document.getElementById('closeCreateUserModal');
            const cancelBtn = document.getElementById('cancelCreateUserModal');

            const openModal = () => modal.classList.add('show');
            const closeModal = () => modal.classList.remove('show');

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            @if ($errors->any())
                openModal();
            @endif
        });
    </script>
</body>
</html>
