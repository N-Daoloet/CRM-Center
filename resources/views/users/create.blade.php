<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">เพิ่มผู้ใช้</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">เพิ่มผู้ใช้ใหม่ในระบบ</p>
                    </div>
                    <a href="{{ route('salesmen.index') }}" class="btn secondary">กลับหน้ารายการผู้ใช้</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('salesmen.store') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group">

                            <label>ชื่อ - นามสกุล</label>
                            <input type="text" name="name" required>
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

                            <label>อีเมล</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">

                            <label>เป้าหมายยอดขาย</label>
                            <input type="number" name="sales_target" min="0" step="1000" placeholder="0">
                        </div>

                        <div class="form-group">

                            <label>เบอร์โทร</label>
                            <input type="text" name="phone" id="phoneInput">
                        </div>

                        <div class="form-group">

                            <label>Supervisor</label>
                            <select name="supervisor_id">
                                <option value="">-- ไม่ระบุ supervisor --</option>
                                @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}">{{ $supervisor->name }} ({{ $supervisor->position ?? 'Supervisor' }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">

                            <label>จังหวัด</label>
                            <select id="provinceSelect" name="province_id">
                                <option value="">เลือกจังหวัด</option>
                            </select>
                        </div>
                        
                        <div class="form-group">

                            <label>อำเภอ/เขต</label>
                            <select id="districtSelect" name="district_id" disabled>
                                <option value="">เลือกอำเภอ/เขต</option>
                            </select>
                        </div>
                        

                        <div class="form-group">

                            <label>ตำบล/แขวง</label>
                            <select id="subdistrictSelect" name="sub_district_id" disabled>
                                <option value="">เลือกตำบล/แขวง</option>
                            </select>
                        </div>
                        
                        
                        <div class="form-group">

                            <label>รหัสไปรษณีย์</label>
                            <input type="text" name="zipcode" placeholder="รหัสไปรษณีย์">
                    
                        </div>

                        
                        <div class="form-group">

                            <label>รายละเอียดที่อยู่</label>
                            <textarea name="address" rows="3" placeholder="บ้านเลขที่ / ซอย / ถนน"></textarea>
                        </div>
                        
                       
                        
                    </div>
                    <div class="modal-actions">
                        <a href="{{ route('salesmen.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const provinceSelect = document.getElementById('provinceSelect');
            const districtSelect = document.getElementById('districtSelect');
            const subdistrictSelect = document.getElementById('subdistrictSelect');
            const phoneInput = document.getElementById('phoneInput');


            if (phoneInput) {
                phoneInput.addEventListener('input', function () {
                    let digits = this.value.replace(/\D/g, '').slice(0, 10);

                    if (digits.length <= 3) {
                        this.value = digits;
                        return;
                    }

                    if (digits.length <= 6) {
                        this.value = digits.slice(0, 3) + '-' + digits.slice(3);
                        return;
                    }

                    this.value = digits.slice(0, 3) + '-' + digits.slice(3, 6) + '-' + digits.slice(6, 10);
                });
            }

            function populateSelect(select, items, placeholder) {
                select.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = placeholder;
                select.appendChild(defaultOption);

                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }

            function resetDependentSelect(select, placeholder) {
                select.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = placeholder;
                select.appendChild(defaultOption);
                select.disabled = true;
            }

            fetch('{{ route('api.provinces') }}')
                .then(response => response.json())
                .then(provinces => {
                    populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');
                    provinceSelect.disabled = false;
                });

            provinceSelect.addEventListener('change', function () {
                const provinceId = this.value;
                resetDependentSelect(districtSelect, 'เลือกอำเภอ/เขต');
                resetDependentSelect(subdistrictSelect, 'เลือกตำบล/แขวง');

                if (!provinceId) return;

                fetch(`{{ route('api.districts') }}?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(districts => {
                        populateSelect(districtSelect, districts, 'เลือกอำเภอ/เขต');
                        districtSelect.disabled = false;
                    });
            });

            districtSelect.addEventListener('change', function () {
                const districtId = this.value;
                resetDependentSelect(subdistrictSelect, 'เลือกตำบล/แขวง');

                if (!districtId) return;

                fetch(`{{ route('api.subdistricts') }}?district_id=${districtId}`)
                    .then(response => response.json())
                    .then(subdistricts => {
                        populateSelect(subdistrictSelect, subdistricts, 'เลือกตำบล/แขวง');
                        subdistrictSelect.disabled = false;
                    });
            });
        });
    </script>
</body>
</html>
