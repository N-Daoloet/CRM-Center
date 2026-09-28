<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขการขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .sales-form-shell { display: flex; flex-direction: column; gap: 16px; }
        .stack-section { background: #fff; border: 1px solid #f3e2d3; border-radius: 16px; padding: 14px; }
        .stack-section h3 { margin: 0 0 10px; color: #9a2c00; }
        .section-divider { height: 1px; background: #f3e2d3; margin: 14px 0; }
        .summary-title { margin: 0 0 10px; color: #9a2c00; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e5e7eb; font-size: 14px; color: #374151; }
        .summary-row strong { color: #111827; }
        .summary-row.total { border-bottom: none; margin-top: 8px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 18px; font-weight: 700; }
        .currency-note { margin: 8px 0 0; color: #6b7280; font-size: 12px; }
        .customer-address-box { margin-top: 8px; background: #fffaf5; border: 1px dashed #f3e2d3; border-radius: 10px; padding: 10px 12px; font-size: 13px; color: #374151; min-height: 42px; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items-table th, .items-table td { border-bottom: 1px solid #f3e2d3; padding: 8px 6px; vertical-align: middle; }
        .items-table th { background: #fff7ed; color: #9a2c00; font-size: 12px; text-transform: uppercase; }
        .remove-item-btn { border: none; background: #fee2e2; color: #b91c1c; width: 28px; height: 28px; border-radius: 999px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card form-card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขการขาย</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">แก้ไขข้อมูลการขายจากรายการจริงในฐานข้อมูล</p>
                    </div>
                    <a href="{{ route('sales.orders') }}" class="btn secondary">กลับหน้ารายการขาย</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('sales.orders.update', $sale) }}">
                    @csrf
                    @method('PUT')

                    @php
                        $saleItems = old('products');
                        if (!$saleItems) {
                            $saleItems = $sale->items->map(fn($item) => ['product_id' => $item->product_id, 'quantity' => $item->quantity])->toArray();
                        }
                        if (empty($saleItems)) {
                            $saleItems = [['product_id' => $sale->product_id, 'quantity' => $sale->quantity]];
                        }
                    @endphp

                    <div class="sales-form-shell">
                        <section class="stack-section">
                            <h3>1. ข้อมูลการขายสินค้า</h3>

                            @if($returnedQty > 0)
                                <div class="alert warning">รายการนี้มีการคืนสินค้าแล้ว {{ $returnedQty }} ชิ้น ไม่สามารถแก้รายการสินค้าได้</div>
                            @endif

                            <div class="form-grid">
                                <label>วันที่ขาย
                                    <input type="date" name="sale_date" value="{{ old('sale_date', $sale->sale_date) }}" required>
                                </label>
                                <label>สถานะ
                                    <select name="status">
                                        <option value="completed" {{ old('status', $sale->status) === 'completed' ? 'selected' : '' }}>completed</option>
                                        <option value="pending" {{ old('status', $sale->status) === 'pending' ? 'selected' : '' }}>pending</option>
                                        <option value="cancelled" {{ old('status', $sale->status) === 'cancelled' ? 'selected' : '' }}>cancelled</option>
                                    </select>
                                </label>
                            </div>

                            <input type="hidden" name="vat_rate" value="7">

                            <div class="section-divider"></div>

                            <h3 style="margin-top:0;">รายการสินค้า</h3>
                            <table class="items-table" id="items_table">
                                <thead>
                                    <tr>
                                        <th>สินค้า</th>
                                        <th style="width:120px;">จำนวน</th>
                                        <th style="width:140px;">ราคา/หน่วย</th>
                                        <th style="width:140px;">รวม</th>
                                        <th style="width:48px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($saleItems as $i => $oldItem)
                                        <tr class="item-row">
                                            <td>
                                                <select name="products[{{ $i }}][product_id]" class="item-product" {{ $returnedQty > 0 ? 'disabled' : '' }} required>
                                                    <option value="">เลือกสินค้า</option>
                                                    @foreach($products as $product)
                                                        @php $salePrice = (float) ($product->sale_price ?? $product->price); @endphp
                                                        <option value="{{ $product->id }}" data-price="{{ $salePrice }}" {{ (string) ($oldItem['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ number_format($salePrice, 2) }}) - คงเหลือ {{ $product->stock }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @if($returnedQty > 0)
                                                    <input type="hidden" name="products[{{ $i }}][product_id]" value="{{ $oldItem['product_id'] }}">
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" name="products[{{ $i }}][quantity]" class="item-qty" min="1" value="{{ (int) ($oldItem['quantity'] ?? 1) }}" {{ $returnedQty > 0 ? 'readonly' : '' }} required>
                                            </td>
                                            <td><input type="text" class="item-price-display" value="0.00" readonly></td>
                                            <td><input type="text" class="item-total-display" value="0.00" readonly></td>
                                            <td>
                                                @if($returnedQty > 0)
                                                    <button type="button" class="remove-item-btn" disabled>-</button>
                                                @else
                                                    <button type="button" class="remove-item-btn" title="ลบแถว">-</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if($returnedQty === 0)
                                <button type="button" id="add_item_btn" class="btn secondary" style="margin-top:10px;">+ เพิ่มสินค้า</button>
                            @endif

                            <div class="section-divider"></div>

                            <h3 class="summary-title" style="margin-top:0;">สรุปราคา</h3>
                            <div class="summary-row">
                                <span>ส่วนลด</span>
                                <input type="number" id="discount_amount" name="discount_amount" min="0" step="0.01" value="{{ old('discount_amount', $sale->discount_amount ?? 0) }}" style="max-width: 180px; text-align: right;">
                            </div>
                            <div class="summary-row"><span>ราคาก่อน VAT</span><strong id="subtotal_display">0.00</strong></div>
                            <div class="summary-row"><span>VAT (7%)</span><strong id="vat_display">0.00</strong></div>
                            <div class="summary-row total"><span>ราคาสุทธิ</span><strong id="grand_total_display">0.00</strong></div>
                            <p class="currency-note">หมายเหตุ: ระบบบันทึก VAT และส่วนลดลงฐานข้อมูลจริง</p>
                        </section>

                        <section class="stack-section">
                            <h3>2. ข้อมูลลูกค้า</h3>
                            <div class="form-grid">
                                <label class="full-span">ลูกค้า
                                    <select name="customer_id" id="customer_id" required>
                                        <option value="">เลือกลูกค้า</option>
                                        @foreach($customers as $customer)
                                            <option
                                                value="{{ $customer->id }}"
                                                data-address="{{ $customer->address ?? '' }}"
                                                data-province-id="{{ $customer->province_id ?? '' }}"
                                                data-district-id="{{ $customer->district_id ?? '' }}"
                                                data-sub-district-id="{{ $customer->sub_district_id ?? '' }}"
                                                data-zipcode="{{ $customer->zipcode ?? '' }}"
                                                {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}
                                            >
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <label>จังหวัด
                                    <select id="shipping_province_id" name="shipping_province_id">
                                        <option value="">เลือกจังหวัด</option>
                                    </select>
                                </label>
                                <label>อำเภอ/เขต
                                    <select id="shipping_district_id" name="shipping_district_id" disabled>
                                        <option value="">เลือกอำเภอ/เขต</option>
                                    </select>
                                </label>
                                <label>ตำบล/แขวง
                                    <select id="shipping_sub_district_id" name="shipping_sub_district_id" disabled>
                                        <option value="">เลือกตำบล/แขวง</option>
                                    </select>
                                </label>
                                <label>รหัสไปรษณีย์
                                    <input type="text" id="shipping_zipcode" name="shipping_zipcode" value="{{ old('shipping_zipcode', $sale->shipping_zipcode ?? ($sale->customer->zipcode ?? '')) }}" placeholder="รหัสไปรษณีย์">
                                </label>
                                <label class="full-span">ที่อยู่ (บ้านเลขที่/ถนน/หมู่บ้าน)
                                    <textarea id="shipping_address_line" name="shipping_address_line" rows="2" class="customer-address-box" style="width:100%; resize: vertical;">{{ old('shipping_address_line', $sale->shipping_address_line ?? ($sale->customer->address ?? '')) }}</textarea>
                                </label>
                                <label class="full-span">ที่อยู่จัดส่ง
                                    <textarea id="shipping_address" name="shipping_address" rows="3" class="customer-address-box" style="width:100%; resize: vertical;">{{ old('shipping_address', $sale->shipping_address ?? ($sale->customer->address ?? '')) }}</textarea>
                                </label>
                                <div class="full-span" style="display:flex; justify-content:flex-end;">
                                    <button type="button" id="compose_shipping_address_btn" class="btn secondary">อัปเดตที่อยู่เต็มจากลำดับขั้น</button>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('sales.orders') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึกการแก้ไข</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function formatMoney(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function populateSelect(select, items, placeholder) {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = placeholder;
            select.appendChild(defaultOption);

            items.forEach(function (item) {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (item.zip_code) {
                    option.dataset.zipCode = item.zip_code;
                }
                select.appendChild(option);
            });
        }

        function resetDependentSelect(select, placeholder) {
            populateSelect(select, [], placeholder);
            select.disabled = true;
        }

        async function fetchDistricts(provinceId) {
            if (!provinceId) return [];
            const response = await fetch(`{{ route('api.districts') }}?province_id=${provinceId}`);
            return response.json();
        }

        async function fetchSubdistricts(districtId) {
            if (!districtId) return [];
            const response = await fetch(`{{ route('api.subdistricts') }}?district_id=${districtId}`);
            return response.json();
        }

        let allowAutoComposeAddress = true;

        function composeShippingAddress(force = false) {
            if (!force && !allowAutoComposeAddress) {
                return;
            }

            const provinceSelect = document.getElementById('shipping_province_id');
            const districtSelect = document.getElementById('shipping_district_id');
            const subdistrictSelect = document.getElementById('shipping_sub_district_id');
            const shippingZipcode = document.getElementById('shipping_zipcode');
            const shippingAddressLine = document.getElementById('shipping_address_line');
            const shippingAddress = document.getElementById('shipping_address');

            const provinceName = provinceSelect.value ? provinceSelect.selectedOptions[0]?.text : '';
            const districtName = districtSelect.value ? districtSelect.selectedOptions[0]?.text : '';
            const subdistrictName = subdistrictSelect.value ? subdistrictSelect.selectedOptions[0]?.text : '';
            const zipcode = shippingZipcode.value.trim();
            const line = shippingAddressLine.value.trim();

            shippingAddress.value = [line, subdistrictName, districtName, provinceName, zipcode]
                .filter(Boolean)
                .join(' ')
                .trim();
        }

        async function loadDistrictAndSubdistrict(provinceId, districtId = '', subDistrictId = '') {
            const districtSelect = document.getElementById('shipping_district_id');
            const subdistrictSelect = document.getElementById('shipping_sub_district_id');
            const shippingZipcode = document.getElementById('shipping_zipcode');

            resetDependentSelect(districtSelect, 'เลือกอำเภอ/เขต');
            resetDependentSelect(subdistrictSelect, 'เลือกตำบล/แขวง');

            if (!provinceId) {
                return;
            }

            const districts = await fetchDistricts(provinceId);
            populateSelect(districtSelect, districts, 'เลือกอำเภอ/เขต');
            districtSelect.disabled = false;

            if (districtId) {
                districtSelect.value = String(districtId);
            }

            if (!districtSelect.value) {
                return;
            }

            const subdistricts = await fetchSubdistricts(districtSelect.value);
            populateSelect(subdistrictSelect, subdistricts, 'เลือกตำบล/แขวง');
            subdistrictSelect.disabled = false;

            if (subDistrictId) {
                subdistrictSelect.value = String(subDistrictId);
            }

            const selectedSub = subdistrictSelect.selectedOptions[0];
            if (selectedSub && selectedSub.dataset.zipCode && !shippingZipcode.value.trim()) {
                shippingZipcode.value = selectedSub.dataset.zipCode;
            }
        }

        async function updateCustomerAddress(force = false) {
            const customerSelect = document.getElementById('customer_id');
            const shippingAddress = document.getElementById('shipping_address');
            const shippingAddressLine = document.getElementById('shipping_address_line');
            const shippingZipcode = document.getElementById('shipping_zipcode');
            const provinceSelect = document.getElementById('shipping_province_id');
            const selected = customerSelect.options[customerSelect.selectedIndex];
            const address = selected && selected.dataset ? (selected.dataset.address || '').trim() : '';
            const provinceId = selected && selected.dataset ? selected.dataset.provinceId || '' : '';
            const districtId = selected && selected.dataset ? selected.dataset.districtId || '' : '';
            const subDistrictId = selected && selected.dataset ? selected.dataset.subDistrictId || '' : '';
            const zipcode = selected && selected.dataset ? selected.dataset.zipcode || '' : '';

            if (force || !shippingAddressLine.value.trim()) {
                shippingAddressLine.value = address;
            }
            if (force || !shippingZipcode.value.trim()) {
                shippingZipcode.value = zipcode;
            }

            if (provinceId) {
                provinceSelect.value = String(provinceId);
            }

            await loadDistrictAndSubdistrict(provinceSelect.value, districtId, subDistrictId);
            allowAutoComposeAddress = true;
            if (force || !shippingAddress.value.trim()) {
                composeShippingAddress(true);
            }
        }

        function attachRowEvents(row) {
            const product = row.querySelector('.item-product');
            const qty = row.querySelector('.item-qty');
            const remove = row.querySelector('.remove-item-btn');
            if (product) product.addEventListener('change', updateSummary);
            if (qty) qty.addEventListener('input', updateSummary);
            if (remove && !remove.disabled) {
                remove.addEventListener('click', function () {
                    const tbody = document.querySelector('#items_table tbody');
                    if (tbody.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                        reindexRows();
                        updateSummary();
                    }
                });
            }
        }

        function reindexRows() {
            document.querySelectorAll('#items_table tbody .item-row').forEach(function (row, index) {
                const product = row.querySelector('.item-product');
                const qty = row.querySelector('.item-qty');
                if (product) product.name = 'products[' + index + '][product_id]';
                if (qty) qty.name = 'products[' + index + '][quantity]';
            });
        }

        function addItemRow() {
            const tbody = document.querySelector('#items_table tbody');
            const firstRow = tbody.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('.item-product').selectedIndex = 0;
            newRow.querySelector('.item-qty').value = 1;
            newRow.querySelector('.item-price-display').value = '0.00';
            newRow.querySelector('.item-total-display').value = '0.00';
            tbody.appendChild(newRow);
            attachRowEvents(newRow);
            reindexRows();
            updateSummary();
        }

        function updateSummary() {
            let subtotal = 0;
            document.querySelectorAll('#items_table tbody .item-row').forEach(function (row) {
                const productSelect = row.querySelector('.item-product');
                const qty = Number(row.querySelector('.item-qty').value || 0);
                const selected = productSelect && productSelect.options ? productSelect.options[productSelect.selectedIndex] : null;
                const unitPrice = Number(selected && selected.dataset ? selected.dataset.price : 0);
                const lineTotal = unitPrice * qty;

                row.querySelector('.item-price-display').value = formatMoney(unitPrice);
                row.querySelector('.item-total-display').value = formatMoney(lineTotal);
                subtotal += lineTotal;
            });

            const discountAmount = Number(document.getElementById('discount_amount').value || 0);
            const vatRate = 7;
            const safeDiscount = Math.min(discountAmount, subtotal);
            const taxable = Math.max(0, subtotal - safeDiscount);
            const vat = taxable * (vatRate / 100);
            const grandTotal = taxable + vat;

            document.getElementById('subtotal_display').textContent = formatMoney(subtotal);
            document.getElementById('vat_display').textContent = formatMoney(vat);
            document.getElementById('grand_total_display').textContent = formatMoney(grandTotal);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const provinceSelect = document.getElementById('shipping_province_id');
            const districtSelect = document.getElementById('shipping_district_id');
            const subdistrictSelect = document.getElementById('shipping_sub_district_id');
            const shippingZipcode = document.getElementById('shipping_zipcode');
            const shippingAddressLine = document.getElementById('shipping_address_line');
            const shippingAddress = document.getElementById('shipping_address');

            document.querySelectorAll('#items_table tbody .item-row').forEach(attachRowEvents);
            const addBtn = document.getElementById('add_item_btn');
            if (addBtn) addBtn.addEventListener('click', addItemRow);
            document.getElementById('customer_id').addEventListener('change', async function () {
                await updateCustomerAddress(true);
            });
            document.getElementById('discount_amount').addEventListener('input', updateSummary);

            fetch('{{ route('api.provinces') }}')
                .then(response => response.json())
                .then(async function (provinces) {
                    populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');

                    const initialProvinceId = '{{ old('shipping_province_id', $sale->shipping_province_id ?? ($sale->customer->province_id ?? '')) }}';
                    const initialDistrictId = '{{ old('shipping_district_id', $sale->shipping_district_id ?? ($sale->customer->district_id ?? '')) }}';
                    const initialSubDistrictId = '{{ old('shipping_sub_district_id', $sale->shipping_sub_district_id ?? ($sale->customer->sub_district_id ?? '')) }}';

                    if (initialProvinceId) {
                        provinceSelect.value = String(initialProvinceId);
                        await loadDistrictAndSubdistrict(initialProvinceId, initialDistrictId, initialSubDistrictId);
                    } else {
                        await updateCustomerAddress(false);
                    }
                    composeShippingAddress(false);
                });

            provinceSelect.addEventListener('change', async function () {
                await loadDistrictAndSubdistrict(this.value);
                composeShippingAddress(false);
            });

            districtSelect.addEventListener('change', async function () {
                const subdistricts = await fetchSubdistricts(this.value);
                populateSelect(subdistrictSelect, subdistricts, 'เลือกตำบล/แขวง');
                subdistrictSelect.disabled = !this.value;
                composeShippingAddress(false);
            });

            subdistrictSelect.addEventListener('change', function () {
                const selected = this.selectedOptions[0];
                if (selected && selected.dataset.zipCode) {
                    shippingZipcode.value = selected.dataset.zipCode;
                }
                composeShippingAddress(false);
            });

            shippingZipcode.addEventListener('input', function () {
                composeShippingAddress(false);
            });

            shippingAddressLine.addEventListener('input', function () {
                composeShippingAddress(false);
            });

            shippingAddress.addEventListener('input', function () {
                allowAutoComposeAddress = false;
            });

            document.getElementById('compose_shipping_address_btn').addEventListener('click', function () {
                allowAutoComposeAddress = true;
                composeShippingAddress(true);
            });

            updateSummary();
        });
    </script>
</body>
</html>
