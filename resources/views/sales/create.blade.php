<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มการขาย</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .sales-form-shell {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .stack-section {
            background: #fff;
            border: 1px solid #f3e2d3;
            border-radius: 16px;
            padding: 14px;
        }

        .stack-section h3 {
            margin: 0 0 10px;
            color: #9a2c00;
        }

        .section-divider {
            height: 1px;
            background: #f3e2d3;
            margin: 14px 0;
        }

        .summary-title { margin: 0 0 10px; color: #9a2c00; }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 14px;
            color: #374151;
        }
        .summary-row strong { color: #111827; }
        .summary-row.total {
            border-bottom: none;
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 18px;
            font-weight: 700;
        }
        .currency-note { margin: 8px 0 0; color: #6b7280; font-size: 12px; }
        .customer-address-box {
            margin-top: 8px;
            background: #fffaf5;
            border: 1px dashed #f3e2d3;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: #374151;
            min-height: 42px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .items-table th,
        .items-table td {
            border-bottom: 1px solid #f3e2d3;
            padding: 8px 6px;
            vertical-align: middle;
        }
        .items-table th {
            background: #fff7ed;
            color: #9a2c00;
            font-size: 12px;
            text-transform: uppercase;
        }
        .remove-item-btn {
            border: none;
            background: #fee2e2;
            color: #b91c1c;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            cursor: pointer;
        }
        .campaign-bundle-products {
            display: none;
            margin-top: 6px;
            padding: 8px 10px;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            background: #fff7ed;
            color: #7c2d12;
            font-size: 12px;
            line-height: 1.6;
        }
        .campaign-bundle-products ul {
            margin: 4px 0 0;
            padding-left: 18px;
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
                        <h1 style="margin:0;">เพิ่มการขาย</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">บันทึกการขายใหม่ลงฐานข้อมูล</p>
                    </div>
                    <a href="{{ route('sales.orders') }}" class="btn secondary">กลับหน้าการขาย</a>
                </div>

                @if($errors->any())
                    <div class="alert error">{{ $errors->first() }}</div>
                @endif

                <form class="modal-form" method="POST" action="{{ route('sales.store') }}">
                    @csrf
                    <div class="sales-form-shell">
                        <section class="stack-section">
                            <h3>1. ข้อมูลการขายสินค้า</h3>

                            <div class="form-grid">
                                <label>วันที่ขาย
                                    <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                                </label>
                                <label>สถานะ
                                    <select name="status">
                                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>completed</option>
                                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>pending</option>
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
                                    @php $oldItems = old('products', [['product_id' => '', 'quantity' => 1]]); @endphp
                                    @foreach($oldItems as $i => $oldItem)
                                        <tr class="item-row">
                                            <td>
                                                <select class="item-product-choice" required>
                                                    <option value="">เลือกสินค้า</option>
                                                </select>
                                                <div class="campaign-bundle-products"></div>
                                                <input type="hidden" class="item-product" name="products[{{ $i }}][product_id]" value="{{ $oldItem['product_id'] ?? '' }}">
                                                <input type="hidden" class="item-campaign" name="products[{{ $i }}][campaign_id]" value="{{ $oldItem['campaign_id'] ?? old("products.$i.campaign_id") }}">
                                            </td>
                                            <td><input type="number" name="products[{{ $i }}][quantity]" class="item-qty" min="1" value="{{ (int) ($oldItem['quantity'] ?? 1) }}" required></td>
                                            <td><input type="text" class="item-price-display" value="0.00" readonly></td>
                                            <td><input type="text" class="item-total-display" value="0.00" readonly></td>
                                            <td><button type="button" class="remove-item-btn" title="ลบแถว">-</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <button type="button" id="add_item_btn" class="btn secondary" style="margin-top:10px;">+ เพิ่มสินค้า</button>

                            <div class="section-divider"></div>

                            <h3 class="summary-title" style="margin-top:0;">สรุปราคา</h3>
                            <div class="summary-row">
                                <span>ส่วนลด</span>
                                <input type="number" id="discount_amount" name="discount_amount" min="0" step="0.01" value="{{ old('discount_amount', 0) }}" style="max-width: 180px; text-align: right;">
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
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}
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
                                    <input type="text" id="shipping_zipcode" name="shipping_zipcode" value="{{ old('shipping_zipcode') }}" placeholder="รหัสไปรษณีย์">
                                </label>
                                <label class="full-span">ที่อยู่ (บ้านเลขที่/ถนน/หมู่บ้าน)
                                    <textarea id="shipping_address_line" name="shipping_address_line" rows="2" class="customer-address-box" style="width:100%; resize: vertical;">{{ old('shipping_address_line') }}</textarea>
                                </label>
                                <!-- <label class="full-span">ที่อยู่จัดส่ง
                                    <textarea id="shipping_address" name="shipping_address" rows="3" class="customer-address-box" style="width:100%; resize: vertical;">{{ old('shipping_address') }}</textarea>
                                </label>
                                <div class="full-span" style="display:flex; justify-content:flex-end;">
                                    <button type="button" id="compose_shipping_address_btn" class="btn secondary">อัปเดตที่อยู่เต็มจากลำดับขั้น</button>
                                </div> -->
                            </div>
                        </section>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('sales.orders') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function formatMoney(value) {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        const campaignData = @json($campaigns);
        const productCatalog = @json($productCatalog);

        function populateItemProductSelect(row) {
            const productSelect = row.querySelector('.item-product-choice');
            const productIdInput = row.querySelector('.item-product');
            const campaignInput = row.querySelector('.item-campaign');
            const previousProductId = productIdInput.value;
            const previousCampaignId = campaignInput.value;

            productSelect.innerHTML = '<option value="">เลือกสินค้า</option>';
            const regularGroup = document.createElement('optgroup');
            regularGroup.label = 'สินค้าทั่วไป';
            productCatalog.forEach(function (product) {
                const option = document.createElement('option');
                option.value = product.id;
                option.dataset.price = product.price;
                option.dataset.productId = product.id;
                option.dataset.campaignId = '';
                option.textContent = product.name + (product.sku ? ' (' + product.sku + ')' : '') + ' - ' + formatMoney(product.price) + ' บาท - คงเหลือ ' + product.stock;
                regularGroup.appendChild(option);
            });
            productSelect.appendChild(regularGroup);

            campaignData.forEach(function (campaign) {
                if (!campaign.products.length) return;
                const campaignGroup = document.createElement('optgroup');
                campaignGroup.label = 'โปรโมชั่นสินค้า';
                const option = document.createElement('option');
                option.value = 'campaign-' + campaign.id;
                option.dataset.price = campaign.price;
                option.dataset.productId = campaign.anchor_product_id;
                option.dataset.campaignId = campaign.id;
                option.textContent = campaign.name + ' — ' + campaign.products.length + ' สินค้าในชุด / ราคาขายหลังลด ' + formatMoney(campaign.price) + ' บาท';
                campaignGroup.appendChild(option);
                productSelect.appendChild(campaignGroup);
            });

            const savedOption = Array.from(productSelect.options).find(option =>
                option.dataset.productId === String(previousProductId) && option.dataset.campaignId === String(previousCampaignId || '')
            );
            if (savedOption) savedOption.selected = true;
            productIdInput.value = savedOption ? savedOption.dataset.productId : '';
            campaignInput.value = savedOption ? savedOption.dataset.campaignId : '';
            row.dataset.lastProductChoice = savedOption ? savedOption.value : '';
            renderCampaignBundleProducts(row, campaignInput.value);
        }

        function getItemChoiceKey(option) {
            if (!option || !option.value) return '';
            return option.dataset.campaignId
                ? 'campaign:' + option.dataset.campaignId
                : 'product:' + option.dataset.productId;
        }

        function syncRowWithChoice(row, option) {
            row.querySelector('.item-product').value = option?.dataset.productId || '';
            row.querySelector('.item-campaign').value = option?.dataset.campaignId || '';
            renderCampaignBundleProducts(row, option?.dataset.campaignId || '');
        }

        function renderCampaignBundleProducts(row, campaignId) {
            const details = row.querySelector('.campaign-bundle-products');
            const campaign = campaignData.find(item => String(item.id) === String(campaignId));
            details.innerHTML = '';

            if (!campaign) {
                details.style.display = 'none';
                return;
            }

            const heading = document.createElement('strong');
            heading.textContent = 'สินค้าในโปรโมชั่น (' + campaign.products.length + ' รายการ)';
            details.appendChild(heading);

            const list = document.createElement('ul');
            campaign.products.forEach(function (product) {
                const listItem = document.createElement('li');
                listItem.textContent = product.name + (product.sku ? ' (' + product.sku + ')' : '');
                list.appendChild(listItem);
            });
            details.appendChild(list);
            details.style.display = 'block';
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
            const productSelect = row.querySelector('.item-product-choice');
            populateItemProductSelect(row);
            productSelect.addEventListener('change', function () {
                const selected = this.selectedOptions[0];
                const choiceKey = getItemChoiceKey(selected);
                const duplicateRow = choiceKey && Array.from(document.querySelectorAll('#items_table tbody .item-row'))
                    .find(otherRow => otherRow !== row && getItemChoiceKey(otherRow.querySelector('.item-product-choice').selectedOptions[0]) === choiceKey);

                if (duplicateRow) {
                    const addQuantity = Number(row.querySelector('.item-qty').value || 1);
                    const duplicateName = selected.textContent.trim();
                    if (window.confirm('เลือก ' + duplicateName + ' ไว้แล้ว ต้องการเพิ่มจำนวนสินค้านี้ใช่หรือไม่?')) {
                        const duplicateQuantity = duplicateRow.querySelector('.item-qty');
                        duplicateQuantity.value = Number(duplicateQuantity.value || 0) + addQuantity;
                        row.remove();
                        reindexRows();
                    } else {
                        this.value = row.dataset.lastProductChoice || '';
                    }
                    syncRowWithChoice(row, this.selectedOptions[0]);
                    updateSummary();
                    return;
                }

                row.dataset.lastProductChoice = this.value;
                syncRowWithChoice(row, selected);
                updateSummary();
            });
            row.querySelector('.item-qty').addEventListener('input', updateSummary);
            row.querySelector('.remove-item-btn').addEventListener('click', function () {
                const tbody = document.querySelector('#items_table tbody');
                if (tbody.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    reindexRows();
                    updateSummary();
                }
            });
        }

        function reindexRows() {
            document.querySelectorAll('#items_table tbody .item-row').forEach(function (row, index) {
                row.querySelector('.item-product-choice').name = '';
                row.querySelector('.item-product').name = 'products[' + index + '][product_id]';
                row.querySelector('.item-campaign').name = 'products[' + index + '][campaign_id]';
                row.querySelector('.item-qty').name = 'products[' + index + '][quantity]';
            });
        }

        function addItemRow() {
            const tbody = document.querySelector('#items_table tbody');
            const firstRow = tbody.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelector('.item-product-choice').selectedIndex = 0;
            newRow.querySelector('.item-product').value = '';
            newRow.querySelector('.item-campaign').value = '';
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
                const productSelect = row.querySelector('.item-product-choice');
                const qty = Number(row.querySelector('.item-qty').value || 0);
                const selected = productSelect.options[productSelect.selectedIndex];
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
            document.getElementById('add_item_btn').addEventListener('click', addItemRow);
            document.getElementById('customer_id').addEventListener('change', async function () {
                await updateCustomerAddress(true);
            });
            document.getElementById('discount_amount').addEventListener('input', updateSummary);

            fetch('{{ route('api.provinces') }}')
                .then(response => response.json())
                .then(async function (provinces) {
                    populateSelect(provinceSelect, provinces, 'เลือกจังหวัด');

                    const initialProvinceId = '{{ old('shipping_province_id') }}';
                    const initialDistrictId = '{{ old('shipping_district_id') }}';
                    const initialSubDistrictId = '{{ old('shipping_sub_district_id') }}';

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

            updateSummary();
        });
    </script>
</body>
</html>
