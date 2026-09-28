<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ request('mode') === 'tax' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' }}</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">{{ request('mode') === 'tax' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' }}</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">สร้างและพิมพ์เอกสารสำหรับลูกค้าได้จากหน้าจอนี้</p>
                    </div>
                    <a href="{{ route('sales.orders') }}" class="btn secondary">กลับรายการขาย</a>
                </div>

                <div class="sales-layout full-width">
                    <div class="card sales-card">
                        <h2>ฟอร์ม{{ request('mode') === 'tax' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' }}</h2>
                        <form class="modal-form form-card">
                            <section class="document-shell">
                                <div class="document-header">
                                    <div>
                                        <div class="document-brand">CRM Center Co., Ltd.</div>
                                        <p>123/45 ถนนสุขุมวิท เขตวัฒนา กรุงเทพมหานคร 10110</p>
                                        <p>โทร. 02-123-4567 · เลขประจำตัวผู้เสียภาษี 3-1000-00000-00-0</p>
                                    </div>
                                    <div class="document-title-block">
                                        <h2>{{ request('mode') === 'tax' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' }}</h2>
                                        <p>เลขที่ INV-2026-001</p>
                                        <p>วันที่ 11 สิงหาคม 2569</p>
                                    </div>
                                </div>

                                <div class="document-meta-grid">
                                    <div class="meta-card">
                                        <h3>ข้อมูลลูกค้า</h3>
                                        <label>ชื่อใน{{ request('mode') === 'tax' ? 'ใบกำกับภาษี' : 'ใบแจ้งหนี้' }}</label>
                                        <input type="text" value="สมชาย ใจดี" placeholder="ระบุชื่อในเอกสาร">

                                        <label>ชื่อลูกค้า</label>
                                        <input type="text" value="สมชาย ใจดี" placeholder="ระบุชื่อลูกค้า">

                                        <label>เลขประจำตัวผู้เสียภาษี</label>
                                        <input type="text" value="3-1000-00000-00-0" placeholder="ระบุเลขภาษี">
                                    </div>

                                    <div class="meta-card">
                                        <h3>ข้อมูลเอกสาร</h3>
                                        <div class="meta-row">
                                            <span>สถานะ</span>
                                            <strong>รอชำระ</strong>
                                        </div>
                                        <div class="meta-row">
                                            <span>เงื่อนไขชำระ</span>
                                            <strong>เงินสด / โอน</strong>
                                        </div>
                                        <div class="meta-row">
                                            <span>วันครบกำหนด</span>
                                            <strong>25 สิงหาคม 2569</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="document-address-grid">
                                    <div class="meta-card">
                                        <h3>ที่อยู่สำหรับออกเอกสาร</h3>
                                        <label>จังหวัด</label>
                                        <select id="provinceSelect" name="province"></select>

                                        <label>อำเภอ/เขต</label>
                                        <select id="districtSelect" name="district"></select>

                                        <label>ตำบล/แขวง</label>
                                        <select id="subdistrictSelect" name="subdistrict"></select>

                                        <label>ที่อยู่</label>
                                        <input type="text" value="123/45 ถนนสุขุมวิท" placeholder="ระบุที่อยู่">
                                    </div>

                                    <div class="meta-card">
                                        <h3>รายละเอียดติดต่อ</h3>
                                        <label>เบอร์โทร</label>
                                        <input type="text" value="081-234-5678" placeholder="ระบุเบอร์โทร">

                                        <label>อีเมล</label>
                                        <input type="email" value="somchai@email.com" placeholder="ระบุอีเมล">

                                        <label>หมายเหตุ</label>
                                        <textarea rows="4" placeholder="ระบุหมายเหตุเพิ่มเติม">กรุณาตรวจสอบรายละเอียดก่อนชำระเงิน</textarea>
                                    </div>
                                </div>

                                <div class="document-table-wrap">
                                    <table class="document-table">
                                        <thead>
                                            <tr>
                                                <th>รายการ</th>
                                                <th>จำนวน</th>
                                                <th>หน่วยละ</th>
                                                <th>จำนวนเงิน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>กล่องสินค้า A</td>
                                                <td>1</td>
                                                <td>45,000.00</td>
                                                <td>45,000.00</td>
                                            </tr>
                                            <tr>
                                                <td>ค่าจัดส่ง</td>
                                                <td>1</td>
                                                <td>3,000.00</td>
                                                <td>3,000.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="document-total-box">
                                    <div class="document-total-row">
                                        <span>ยอดรวม</span>
                                        <strong>฿48,000</strong>
                                    </div>
                                    <div class="document-total-row">
                                        <span>VAT 7%</span>
                                        <strong>฿3,360</strong>
                                    </div>
                                    <div class="document-total-row final">
                                        <span>ยอดชำระสุทธิ</span>
                                        <strong>฿51,360</strong>
                                    </div>
                                </div>

                                <div class="signature-grid">
                                    <div class="signature-box">
                                        <label>ผู้อนุมัติ</label>
                                        <input type="text" value="นางสาวพิมพ์ใจ" placeholder="ชื่อผู้อนุมัติ">
                                        <span class="signature-line"></span>
                                    </div>
                                    <div class="signature-box">
                                        <label>ผู้รับเอกสาร</label>
                                        <input type="text" value="สมชาย ใจดี" placeholder="ชื่อผู้รับเอกสาร">
                                        <span class="signature-line"></span>
                                    </div>
                                </div>

                                <div class="modal-actions">
                                    <a href="{{ route('sales.orders') }}" class="btn secondary">ยกเลิก</a>
                                    <button type="button" class="btn print-btn" onclick="window.print()">🖨️ ปริ้นเอกสาร</button>
                                    <button type="submit" class="btn">บันทึก</button>
                                </div>
                            </section>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const provinceSelect = document.getElementById('provinceSelect');
        const districtSelect = document.getElementById('districtSelect');
        const subdistrictSelect = document.getElementById('subdistrictSelect');

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

        function resetSelect(select, placeholder) {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = placeholder;
            select.appendChild(defaultOption);
        }

        fetch('{{ route('api.provinces') }}')
            .then(response => response.json())
            .then(provinces => {
                populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');

                provinceSelect.addEventListener('change', function () {
                    const provinceId = this.value;
                    resetSelect(districtSelect, 'เลือกอำเภอ/เขต');
                    resetSelect(subdistrictSelect, 'เลือกตำบล/แขวง');

                    if (!provinceId) return;

                    fetch(`{{ route('api.districts') }}?province_id=${provinceId}`)
                        .then(response => response.json())
                        .then(districts => populateSelect(districtSelect, districts, 'เลือกอำเภอ/เขต'));
                });

                districtSelect.addEventListener('change', function () {
                    const districtId = this.value;
                    resetSelect(subdistrictSelect, 'เลือกตำบล/แขวง');

                    if (!districtId) return;

                    fetch(`{{ route('api.subdistricts') }}?district_id=${districtId}`)
                        .then(response => response.json())
                        .then(subdistricts => populateSelect(subdistrictSelect, subdistricts, 'เลือกตำบล/แขวง'));
                });
            });
    </script>
</body>
</html>
