<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขลูกค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขลูกค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">ปรับปรุงข้อมูลลูกค้าและที่อยู่</p>
                    </div>
                    <a href="{{ route('customers.index') }}" class="btn secondary">กลับหน้าลูกค้า</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form customer-form" method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf
                    @method('PUT')

                    <div class="customer-form-grid">
                        <div class="customer-form-column">
                            <label>ชื่อเต็ม
                                <input type="text" name="name" value="{{ old('name', $customer->name) }}" placeholder="กรอกชื่อลูกค้า" required>
                            </label>
                            <label>ชื่อเต็ม (ภาษาอังกฤษ) (ถ้ามี)
                                <input type="text" name="english_name" value="{{ old('english_name', $customer->english_name ?? '') }}" placeholder="กรอกชื่อภาษาอังกฤษ">
                            </label>

                            <label>เบอร์โทร
                                <input type="text" name="phone" id="phoneInput" value="{{ old('phone', $customer->phone) }}" placeholder="081-234-5678">
                            </label>
                            <label>อีเมล
                                <input type="email" name="email" value="{{ old('email', $customer->email) }}" placeholder="example@email.com" required>
                            </label>
                            <label>ประเภทลูกค้า
                                <select name="flag" required>
                                    <option value="">ประเภทลูกค้า</option>
                                    <option value="1" {{ old('flag', $customer->flag) == 1 ? 'selected' : '' }}>ประจำ</option>
                                    <option value="2" {{ old('flag', $customer->flag) == 2 ? 'selected' : '' }}>ส่วนตัว</option>
                                    <option value="3" {{ old('flag', $customer->flag) == 3 ? 'selected' : '' }}>ลูกค้าใหม่</option>
                                    <option value="4" {{ old('flag', $customer->flag) == 4 ? 'selected' : '' }}>ลูกค้าถังกลาง</option>
                                    <option value="5" {{ old('flag', $customer->flag) == 5 ? 'selected' : '' }}>เคยเป็น</option>
                                    <option value="6" {{ old('flag', $customer->flag) == 6 ? 'selected' : '' }}>ติดตาม</option>
                                </select>
                            </label>
                            <label>ช่องทาง
                                <select name="channel" required>
                                    <option value="">ช่องทาง</option>
                                    <option value="1" {{ old('channel', $customer->channel) == 1 ? 'selected' : '' }}>Shopee</option>
                                    <option value="2" {{ old('channel', $customer->channel) == 2 ? 'selected' : '' }}>Lazada</option>
                                </select>
                            </label>
                            <label>สถานะ
                                <select name="status" required>
                                    <option value="">สถานะ</option>
                                    <option value="active1" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="2" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                                <textarea id="addressDetail" name="address" rows="4" placeholder="บ้านเลขที่ / ซอย / ถนน">{{ old('address', $customer->address) }}</textarea>
                            </label>

                            <label>รหัสไปรษรีย์
                                <input type="text" name="zipcode" value="{{ old('zipcode', $customer->zipcode ?? '') }}" placeholder="รหัสไปรษรีย์" required>
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

            if (form) {
                form.addEventListener('submit', function () {
                    const provinceName = provinceSelect?.selectedOptions[0]?.text || '';
                    const districtName = districtSelect?.selectedOptions[0]?.text || '';
                    const subdistrictName = subdistrictSelect?.selectedOptions[0]?.text || '';
                    const detail = addressDetail?.value?.trim() || '';

                    if (detail || provinceName || districtName || subdistrictName) {
                        const parts = [detail, provinceName, districtName, subdistrictName].filter(Boolean);
                        addressDetail.value = parts.join(', ');
                    }
                });
            }

            if (!provinceSelect || !districtSelect || !subdistrictSelect) return;

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

            const savedProvinceId = {{ (int) ($customer->province_id ?? 0) }};
            const savedDistrictId = {{ (int) ($customer->district_id ?? 0) }};
            const savedSubDistrictId = {{ (int) ($customer->sub_district_id ?? 0) }};

            fetch('{{ route('api.provinces') }}')
                .then(response => response.json())
                .then(provinces => {
                    populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');
                    provinceSelect.disabled = false;

                    if (savedProvinceId) {
                        provinceSelect.value = String(savedProvinceId);
                    }

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

                                if (savedProvinceId && provinceId == String(savedProvinceId) && savedDistrictId) {
                                    districtSelect.value = String(savedDistrictId);
                                }

                                const currentDistrictId = districtSelect.value;
                                if (currentDistrictId) {
                                    fetch(`{{ route('api.subdistricts') }}?district_id=${currentDistrictId}`)
                                        .then(response => response.json())
                                        .then(subdistricts => {
                                            populateSelect(subdistrictSelect, subdistricts, 'เลือกตำบล/แขวง');
                                            subdistrictSelect.disabled = false;

                                            if (savedDistrictId && currentDistrictId == String(savedDistrictId) && savedSubDistrictId) {
                                                subdistrictSelect.value = String(savedSubDistrictId);
                                            }
                                        });
                                }
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

                                if (savedDistrictId && districtId == String(savedDistrictId) && savedSubDistrictId) {
                                    subdistrictSelect.value = String(savedSubDistrictId);
                                }
                            });
                    });

                    if (savedProvinceId) {
                        provinceSelect.dispatchEvent(new Event('change'));
                    }
                })
                .catch(() => {
                    populateSelect(provinceSelect, [], 'ไม่สามารถโหลดข้อมูลได้');
                    provinceSelect.disabled = true;
                });
        });
    </script>
</body>
</html>
