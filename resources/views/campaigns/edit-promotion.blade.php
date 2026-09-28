<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรโมชั่นสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .edit-section { margin-top:24px; padding:20px; border:1px solid #e5e7eb; border-radius:12px; background:#fff; }
        .edit-table { width:100%; border-collapse:collapse; }
        .edit-table th, .edit-table td { padding:10px; border-bottom:1px solid #e5e7eb; text-align:left; }
        .edit-table th { background:#f9fafb; }
        .edit-table select { width:100%; min-width:220px; }
        .remove-product-row { border:0; border-radius:8px; padding:7px 10px; background:#fee2e2; color:#b91c1c; cursor:pointer; }
        @media (max-width:700px) { .edit-table-wrap { overflow-x:auto; } .edit-table { min-width:600px; } }
    </style>
</head>
<body>
    <div class="page">
        @include('partials.sidebar')
        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">แก้ไขโปรโมชั่นสินค้า</h1>
                        <p style="margin:4px 0 0; color:#6b7280;">แก้ไขรายละเอียดโปรโมชั่นและสินค้าที่ร่วมรายการ</p>
                    </div>
                    <a href="{{ route('campaigns.index') }}" class="btn secondary">กลับหน้ารายการ</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('campaigns.updatePromotion', $campaign) }}">
                    @csrf
                    @method('PUT')
                    @include('campaigns.partials.edit-details', ['campaignTypeLabel' => 'โปรโมชั่นสินค้า'])

                    <section class="edit-section">
                        <h3 style="margin:0 0 12px;">สินค้าในโปรโมชั่น</h3>
                        <p style="margin:0 0 14px; color:#6b7280;">เลือกสินค้าเป็นแถว เพิ่มหรือลบรายการได้เหมือนหน้าสร้างโปรโมชั่น</p>
                        <div style="display:flex; justify-content:flex-end; margin-bottom:10px;">
                            <button type="button" class="btn secondary" id="addProductRowBtn">+ เพิ่มแถวสินค้า</button>
                        </div>
                        <div class="edit-table-wrap">
                            <table class="edit-table campaign-product-table">
                                <thead>
                                    <tr><th>สินค้า</th><th>ราคาปกติ</th><th style="width:90px;">จัดการ</th></tr>
                                </thead>
                                <tbody id="campaignProductRows"></tbody>
                            </table>
                        </div>

                        <div style="margin-top:16px; padding-top:14px; border-top:1px solid #e5e7eb; display:flex; flex-direction:column; gap:14px;">
                            <div style="display:flex; align-items:center; gap:15px; width:100%; max-width:560px; flex-wrap:wrap;">
                                <span style="font-weight:600; white-space:nowrap;">รูปแบบราคา</span>
                                <select id="sharedPriceType" name="pricing_type" style="min-width:140px; flex:1; max-width:180px;">
                                    <option value="percent" {{ old('pricing_type', $pricingType) === 'percent' ? 'selected' : '' }}>ลดเป็นเปอร์เซ็นต์ (%)</option>
                                    <option value="fixed" {{ old('pricing_type', $pricingType) === 'fixed' ? 'selected' : '' }}>กำหนดยอดรวมเอง</option>
                                </select>
                                <input id="sharedPriceValue" name="pricing_value" type="number" min="0" step="0.01" value="{{ old('pricing_value', $pricingValue) }}" style="width:160px;" aria-label="มูลค่าส่วนลดหรือยอดรวม">
                            </div>
                            <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; color:#4b5563;">
                                <span>ราคาก่อนลด: <strong id="campaignOriginalTotal" style="color:#111827;">฿0.00</strong></span>
                                <span>ยอดส่วนลด: <strong id="campaignDiscountTotal" style="color:#dc2626;">฿0.00</strong></span>
                                <span>ราคารวม: <strong id="campaignTotal" style="color:#111827; font-size:1.1rem;">฿0.00</strong></span>
                            </div>
                        </div>
                    </section>

                    <div class="modal-actions">
                        <a href="{{ route('campaigns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึกโปรโมชั่น</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        const productOptions = @json($productOptions);
        const initialProducts = @json(old('products', $initialProducts));
        let nextProductRowIndex = 0;

        function formatPrice(value) {
            return `฿${Number(value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function updateProductRow(selectElement, keepSavedPrice = false) {
            const row = selectElement.closest('tr');
            const product = productOptions.find(item => String(item.id) === String(selectElement.value));
            const priceCell = row.querySelector('.price-original');
            const specialPriceInput = row.querySelector('.product-special-price-input');

            if (!product) {
                priceCell.textContent = '-';
                specialPriceInput.value = '';
                updatePricingSummary();
                return;
            }

            priceCell.textContent = formatPrice(product.price);
            if (!keepSavedPrice || !specialPriceInput.value) {
                specialPriceInput.value = Number(product.price || 0).toFixed(2);
            }
            updatePricingSummary();
        }

        function calculateDiscountedTotal(originalTotal, type, value) {
            if (type === 'percent') {
                const discountPercent = Math.min(Math.max(Number(value || 0), 0), 100);
                return originalTotal * (1 - (discountPercent / 100));
            }

            return Number(value || 0);
        }

        function updatePricingSummary() {
            const originalTotal = Array.from(document.querySelectorAll('.campaign-product-select'))
                .reduce((total, select) => {
                    const product = productOptions.find(item => String(item.id) === String(select.value));
                    return total + (product ? Number(product.price || 0) : 0);
                }, 0);
            const type = document.getElementById('sharedPriceType').value;
            const value = Number(document.getElementById('sharedPriceValue').value || 0);
            const finalTotal = calculateDiscountedTotal(originalTotal, type, value);
            const discount = Math.max(originalTotal - finalTotal, 0);

            document.getElementById('campaignOriginalTotal').textContent = formatPrice(originalTotal);
            document.getElementById('campaignDiscountTotal').textContent = formatPrice(discount);
            document.getElementById('campaignTotal').textContent = formatPrice(finalTotal);
        }

        function addProductRow(productId = '', specialPrice = '') {
            const tbody = document.getElementById('campaignProductRows');
            const rowIndex = nextProductRowIndex++;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td style="padding:10px;">
                    <select name="products[${rowIndex}][product_id]" class="campaign-product-select" aria-label="เลือกสินค้า">
                        <option value="">-- เลือกสินค้า --</option>
                    </select>
                    <input type="hidden" name="products[${rowIndex}][special_price]" class="product-special-price-input" value="${specialPrice}">
                </td>
                <td class="price-original" style="padding:10px;">-</td>
                <td style="padding:10px;"><button type="button" class="remove-product-row">ลบ</button></td>
            `;

            const select = row.querySelector('.campaign-product-select');
            productOptions.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${product.sku || '-'})`;
                select.appendChild(option);
            });
            select.value = productId;
            select.addEventListener('change', () => updateProductRow(select));
            row.querySelector('.remove-product-row').addEventListener('click', () => {
                row.remove();
                if (!tbody.querySelector('tr')) {
                    addProductRow();
                }
                updatePricingSummary();
            });

            tbody.appendChild(row);
            if (productId) {
                updateProductRow(select, true);
            } else {
                updatePricingSummary();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('addProductRowBtn').addEventListener('click', () => addProductRow());
            document.getElementById('sharedPriceType').addEventListener('change', updatePricingSummary);
            document.getElementById('sharedPriceValue').addEventListener('input', updatePricingSummary);

            const selectedProducts = Array.isArray(initialProducts)
                ? initialProducts.filter(item => item && item.product_id)
                : [];

            if (selectedProducts.length) {
                selectedProducts.forEach(item => addProductRow(item.product_id, item.special_price ?? ''));
            } else {
                addProductRow();
            }
        });
    </script>
</body>
</html>