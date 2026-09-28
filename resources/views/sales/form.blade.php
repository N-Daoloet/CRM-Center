<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mode === 'edit' ? 'แก้ไขรายการขาย' : 'เพิ่มรายการขาย' }}</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="sales-layout full-width">
                <div class="card sales-card">
                    <div class="topbar">
                        <div>
                            <h1 style="margin:0;">{{ $mode === 'edit' ? 'แก้ไขรายการขาย' : 'เพิ่มรายการขาย' }}</h1>
                            <p style="margin: 4px 0 0; color: #6b7280;">กรอกข้อมูลลูกค้าและรายละเอียดการขายในฟอร์มเดียว</p>
                        </div>
                        <a href="{{ route('sales.orders') }}" class="btn secondary">กลับรายการขาย</a>
                    </div>

                    <form class="modal-form form-card">
                        <div class="form-section">
                            <h3>ข้อมูลลูกค้า</h3>
                            <div class="form-grid">
                                <label>ชื่อลูกค้า
                                    <input type="text" value="สมชาย ใจดี" placeholder="ระบุชื่อลูกค้า">
                                </label>
                                <label>เบอร์โทร
                                    <input type="text" value="081-234-5678" placeholder="ระบุเบอร์โทร">
                                </label>
                                <label>อีเมล
                                    <input type="email" value="somchai@email.com" placeholder="ระบุอีเมล">
                                </label>
                                <label>ประเภทลูกค้า
                                    <select>
                                        <option>ลูกค้าทั่วไป</option>
                                        <option>ลูกค้าองค์กร</option>
                                        <option>ลูกค้า VIP</option>
                                    </select>
                                </label>
                                <label>จังหวัด
                                    <select id="provinceSelect" name="province"></select>
                                </label>
                                <label>อำเภอ/เขต
                                    <select id="districtSelect" name="district"></select>
                                </label>
                                <label>ตำบล/แขวง
                                    <select id="subdistrictSelect" name="subdistrict"></select>
                                </label>
                                <label class="full-span">รายละเอียดที่อยู่
                                    <textarea rows="3" placeholder="ระบุที่อยู่ของลูกค้า">123/45 ถนนสุขุมวิท กรุงเทพมหานคร</textarea>
                                </label>
                                <label>เลขประจำตัวผู้เสียภาษี
                                    <input type="text" placeholder="ระบุเลขภาษีหากมี">
                                </label>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>ข้อมูลการขาย</h3>
                            <div class="form-grid">
                                <label>เลขออเดอร์
                                    <input type="text" value="#SO-1001" placeholder="ระบุเลขออเดอร์">
                                </label>
                                <label class="full-span">รายการสินค้า
                                    <div class="product-table-wrap">
                                        <table class="product-table">
                                            <thead>
                                                <tr>
                                                    <th>สินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>ราคาต่อหน่วย</th>
                                                    <th>รวม</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="productRows"></tbody>
                                        </table>
                                        <button type="button" class="btn secondary add-row-btn" id="addProductRow">+ เพิ่มรายการ</button>
                                    </div>
                                    <small class="form-note">สามารถเลือกสินค้าหลายรายการแบบแยกบรรทัดได้</small>
                                </label>
                                <label>ยอดเงิน
                                    <input type="text" value="฿45,000" placeholder="ระบุยอดเงิน">
                                </label>
                                <label>วันที่ขาย
                                    <input type="date" value="2026-08-06">
                                </label>
                                <label>สถานะ
                                    <select>
                                        <option>สำเร็จ</option>
                                        <option>รอการชำระ</option>
                                        <option>กำลังจัดส่ง</option>
                                    </select>
                                </label>
                                <label>ผู้ดูแล/พนักงานขาย
                                    <input type="text" value="ธีรพงศ์ แสงดาว" placeholder="ระบุชื่อพนักงานขาย">
                                </label>
                                <label class="full-span">หมายเหตุ
                                    <input type="text" placeholder="ระบุหมายเหตุเพิ่มเติม">
                                </label>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <a href="{{ route('sales.orders') }}" class="btn secondary">ยกเลิก</a>
                            <button type="button" class="btn print-btn" onclick="printOrderSummary()">🖨️ ปริ้นใบสั่งซื้อ</button>
                            <button type="submit" class="btn">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        const provinceSelect = document.getElementById('provinceSelect');
        const districtSelect = document.getElementById('districtSelect');
        const subdistrictSelect = document.getElementById('subdistrictSelect');
        const productRows = document.getElementById('productRows');
        const addProductRowBtn = document.getElementById('addProductRow');

        const products = [
            { name: 'กล่องสินค้า A', price: 1200 },
            { name: 'บริการติดตั้ง', price: 3500 },
            { name: 'สินค้า B', price: 850 }
        ];

        function createRow() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="product-select">
                        <option value="">-- เลือกสินค้า --</option>
                        ${products.map(product => `<option value="${product.name}" data-price="${product.price}">${product.name}</option>`).join('')}
                    </select>
                </td>
                <td><input type="number" class="qty-input" min="1" value="1" placeholder="จำนวน"></td>
                <td><input type="text" class="price-input" placeholder="ราคาต่อหน่วย"></td>
                <td><span class="row-total">฿0</span></td>
                <td><button type="button" class="remove-row-btn" onclick="this.closest('tr').remove(); updateGrandTotal();">×</button></td>
            `;

            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const priceInput = row.querySelector('.price-input');
            const rowTotal = row.querySelector('.row-total');

            select.addEventListener('change', function () {
                const selected = products.find(item => item.name === this.value);
                if (selected) {
                    priceInput.value = selected.price.toLocaleString();
                }
                updateRowTotal(rowTotal, qtyInput, priceInput);
            });

            [qtyInput, priceInput].forEach(input => {
                input.addEventListener('input', () => updateRowTotal(rowTotal, qtyInput, priceInput));
            });

            productRows.appendChild(row);
            updateGrandTotal();
        }

        function updateRowTotal(rowTotal, qtyInput, priceInput) {
            const quantity = parseInt(qtyInput?.value || '0', 10);
            const priceText = (priceInput?.value || '').replace(/,/g, '').replace(/[^0-9.]/g, '');
            const price = parseFloat(priceText) || 0;
            rowTotal.textContent = `฿${(quantity * price).toLocaleString()}`;
            updateGrandTotal();
        }

        function updateGrandTotal() {
            const totals = Array.from(productRows.querySelectorAll('.row-total'));
            const grand = totals.reduce((sum, item) => {
                const value = parseFloat(item.textContent.replace(/[^0-9.]/g, '')) || 0;
                return sum + value;
            }, 0);
            const totalField = document.getElementById('saleTotal');
            if (totalField) {
                totalField.value = `฿${grand.toLocaleString()}`;
            }
        }

        function printOrderSummary() {
            const customerName = document.querySelector('input[placeholder="ระบุชื่อลูกค้า"]').value;
            const customerPhone = document.querySelector('input[placeholder="ระบุเบอร์โทร"]').value;
            const customerEmail = document.querySelector('input[placeholder="ระบุอีเมล"]').value;
            const orderNo = document.querySelector('input[placeholder="ระบุเลขออเดอร์"]').value;
            const saleDate = document.querySelector('input[type="date"]').value;
            const address = document.querySelector('textarea[placeholder="ระบุที่อยู่ของลูกค้า"]').value;
            const taxId = document.querySelector('input[placeholder="ระบุเลขภาษีหากมี"]').value;
            const salesperson = document.querySelector('input[placeholder="ระบุชื่อพนักงานขาย"]').value;
            const note = document.querySelector('input[placeholder="ระบุหมายเหตุเพิ่มเติม"]').value;
            const province = provinceSelect?.value || '-';
            const district = districtSelect?.value || '-';
            const subdistrict = subdistrictSelect?.value || '-';
            const rows = Array.from(productRows.querySelectorAll('tr'));
            const items = rows.map(row => {
                const product = row.querySelector('.product-select').value;
                const qty = row.querySelector('.qty-input').value;
                const price = row.querySelector('.price-input').value;
                return { product, qty, price };
            }).filter(item => item.product);

            const subtotal = items.reduce((sum, item) => {
                const priceValue = parseFloat((item.price || '').replace(/,/g, '').replace(/[^0-9.]/g, '')) || 0;
                const qtyValue = parseInt(item.qty || 0, 10) || 0;
                return sum + (priceValue * qtyValue);
            }, 0);
            const vat = subtotal * 0.07;
            const total = subtotal + vat;

            const printWindow = window.open('', '', 'width=900,height=1000');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>ใบสั่งซื้อ</title>
                        <style>
                            body { font-family: 'Tahoma', Arial, sans-serif; margin: 0; padding: 28px; color: #111827; }
                            .header { border-bottom: 2px solid #f97316; padding-bottom: 12px; margin-bottom: 14px; }
                            .company { font-size: 22px; font-weight: 700; color: #9a2c00; }
                            .sub { color: #6b7280; font-size: 13px; margin-top: 4px; }
                            .section { margin-bottom: 14px; }
                            .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
                            .box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 8px; }
                            th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                            th { background: #fff7ed; color: #9a2c00; }
                            .total-row { font-weight: 700; }
                            .footer { margin-top: 18px; color: #374151; font-size: 13px; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div class="company">CRM Center</div>
                            <div class="sub">ใบสั่งซื้อ / Purchase Order</div>
                        </div>

                        <div class="section grid">
                            <div class="box">
                                <strong>ข้อมูลลูกค้า</strong>
                                <div>ชื่อลูกค้า: ${customerName || '-'}</div>
                                <div>เบอร์โทร: ${customerPhone || '-'}</div>
                                <div>อีเมล: ${customerEmail || '-'}</div>
                                <div>เลขประจำตัวผู้เสียภาษี: ${taxId || '-'}</div>
                            </div>
                            <div class="box">
                                <strong>ข้อมูลใบสั่งซื้อ</strong>
                                <div>เลขออเดอร์: ${orderNo || '-'}</div>
                                <div>วันที่: ${saleDate || '-'}</div>
                                <div>ผู้ดูแล: ${salesperson || '-'}</div>
                                <div>สถานะ: ${note || '-'}</div>
                            </div>
                        </div>

                        <div class="section box">
                            <strong>ที่อยู่จัดส่ง</strong>
                            <div>${province} / ${district} / ${subdistrict}</div>
                            <div>${address || '-'}</div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>สินค้า</th>
                                    <th>จำนวน</th>
                                    <th>ราคาต่อหน่วย</th>
                                    <th>รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map((item, index) => `<tr><td>${index + 1}</td><td>${item.product}</td><td>${item.qty}</td><td>${item.price || '-'}</td><td>฿${(parseInt(item.qty || 0) * parseFloat((item.price || '').replace(/,/g, '').replace(/[^0-9.]/g, '')) || 0).toLocaleString()}</td></tr>`).join('')}
                                <tr class="total-row"><td colspan="4">ยอดก่อนภาษี</td><td>฿${subtotal.toLocaleString()}</td></tr>
                                <tr class="total-row"><td colspan="4">ภาษีมูลค่าเพิ่ม 7%</td><td>฿${vat.toLocaleString()}</td></tr>
                                <tr class="total-row"><td colspan="4">ยอดรวมสุทธิ</td><td>฿${total.toLocaleString()}</td></tr>
                            </tbody>
                        </table>

                        <div class="footer">
                            <div><strong>หมายเหตุ:</strong> ${note || '—'}</div>
                            <div style="margin-top: 16px;">ลงชื่อ...............................................</div>
                        </div>                    </body>
                </html>
            `);            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        addProductRowBtn?.addEventListener('click', createRow);
        createRow();

        if (provinceSelect && districtSelect && subdistrictSelect) {
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
        }
    </script>

</body>
</html>
