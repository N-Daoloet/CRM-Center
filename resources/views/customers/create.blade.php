<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มลูกค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">เพิ่มลูกค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">สร้างข้อมูลลูกค้าใหม่ด้วยรายละเอียดครบถ้วน</p>
                    </div>
                    <a href="{{ route('customers.index') }}" class="btn secondary">กลับหน้าลูกค้า</a>
                </div>

                <form class="modal-form customer-form" method="POST" action="{{ route('customers.store') }}">
                    @csrf

                    <div class="customer-form-grid">
                        <div class="customer-form-column">
                            <label>ชื่อเต็ม
                                <input type="text" name="name" placeholder="กรอกชื่อลูกค้า" required>
                            </label>
                            <label>ชื่อเต็ม (ภาษาอังกฤษ) (ถ้ามี)
                                <input type="text" name="name" placeholder="กรอกชื่อลูกค้า" required>
                            </label>

                            <label>เบอร์โทร
                                <input type="text" name="phone" id="phoneInput" placeholder="081-234-5678">
                            </label>
                            <label>อีเมล
                                <input type="email" name="email" placeholder="example@email.com" required>
                            </label>
                            <label>ประเภทลูกค้า
                                <select id="" name="flag" required>
                                    <option value="">ประเภทลูกค้า</option>
                                    <option value="1">ประจำ</option>
                                    <option value="2">ส่วนตัว</option>
                                    <option value="3">ลูกค้าใหม่</option>
                                    <option value="4">ลูกค้าถังกลาง</option>
                                    <option value="5">เคยเป็น</option>
                                    <option value="6">ติดตาม</option>
                                </select>
                            </label>
                            <label>ช่องทาง
                                <select id="" name="channel" required>
                                    <option value="">ช่องทาง</option>
                                    <option value="1">Shopee</option>
                                    <option value="2">Lazada</option>
                                </select>
                            </label>
                            <label>สถานะลูกค้า
                                <select name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </label>
                            
                        </div>

                        <div class="customer-form-column">
                            
                            <label>จังหวัด
                                <select id="provinceSelect" name="province_id">
                                    <option value="">เลือกจังหวัด</option>
                                </select>
                            </label>

                            <label>อำเภอ/เขต
                                <select id="districtSelect" name="district_id" disabled>
                                    <option value="">เลือกอำเภอ/เขต</option>
                                </select>
                            </label>
                            <label>ตำบล/แขวง
                                <select id="subdistrictSelect" name="sub_district_id" disabled>
                                    <option value="">เลือกตำบล/แขวง</option>
                                </select>
                            </label>

                            <label>รายละเอียดที่อยู่
                                <textarea id="addressDetail" name="address" rows="4" placeholder="บ้านเลขที่ / ซอย / ถนน"></textarea>
                            </label>

                            <label>รหัสไปรษรีย์
                                <input type="text" name="zipcode" placeholder="รหัสไปรษรีย์" required>
                            </label>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('customers.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.modal-form');
            const phoneInput = document.getElementById('phoneInput');
            const provinceSelect = document.getElementById('provinceSelect');
            const districtSelect = document.getElementById('districtSelect');
            const subdistrictSelect = document.getElementById('subdistrictSelect');
            const addressDetail = document.getElementById('addressDetail');

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

            if (!provinceSelect || !districtSelect || !subdistrictSelect || !form) return;

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

            function resetDependentSelect(select, placeholder, disable = true) {
                select.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = placeholder;
                select.appendChild(defaultOption);
                select.disabled = disable;
            }

            function buildAddress() {
                const provinceName = provinceSelect.selectedOptions[0]?.text || '';
                const districtName = districtSelect.selectedOptions[0]?.text || '';
                const subdistrictName = subdistrictSelect.selectedOptions[0]?.text || '';
                const detail = addressDetail?.value?.trim() || '';

                const parts = [detail, provinceName, districtName, subdistrictName].filter(Boolean);
                addressDetail.value = parts.join(', ');
            }

            form.addEventListener('submit', buildAddress);

            fetch('{{ route('api.provinces') }}')
                .then(response => response.json())
                .then(provinces => {
                    populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');
                    provinceSelect.disabled = false;

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
                })
                .catch(() => {
                    populateSelect(provinceSelect, [], 'ไม่สามารถโหลดข้อมูลได้');
                    provinceSelect.disabled = true;
                });
        });
    </script>
</body>
</html>
