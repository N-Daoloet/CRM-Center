<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างแคมเปญสินค้า</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
    <div class="page">
        @include('partials.sidebar')

        <main class="content">
            <div class="card">
                <div class="topbar">
                    <div>
                        <h1 style="margin:0;">สร้างโปรโมชั่นสินค้า</h1>
                        <p style="margin: 4px 0 0; color: #6b7280;">เลือกสินค้าที่ต้องการให้ร่วมแคมเปญและกำหนดราคาพิเศษ</p>
                    </div>
                    <a href="{{ route('campaigns.index') }}" class="btn secondary">กลับหน้ารายการ</a>
                </div>

                <form class="modal-form" method="POST" action="{{ route('campaigns.store') }}">
                    @csrf

                    <div class="form-grid">
                        <label class="full-span">ชื่อโปรโมชั่น
                            <input type="text" name="name" placeholder="เช่น โปรโมชั่นปลายปี" required>
                        </label>

                       

                        <label>วันที่เริ่มต้น
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required>
                        </label>

                        <label>วันที่สิ้นสุด
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                        </label>
                        <label>ประเภทแคมเปญ
                            <input type="text" value="โปรโมชั่นสินค้า" readonly>
                        </label>
                        <label>สถานะ
                            <select name="status">
                                <option value="active">active</option>
                                <option value="planned">planned</option>
                                <option value="completed">completed</option>
                            </select>
                        </label>

                        <label class="full-span">รายละเอียดโปรโมชั่น
                            <textarea rows="4" name="description" placeholder="ระบุเงื่อนไขหรือคำอธิบายเพิ่มเติม"></textarea>
                        </label>
                    </div>

                    <div class="card" style="margin-top: 24px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 16px; flex-wrap: wrap;">
                            <h3 style="margin:0;">สินค้าในโปรโมชั่น</h3>
                            <button type="button" class="btn secondary" onclick="addProductRow()">+ เพิ่มแถว</button>
                        </div>

                        <table class="campaign-product-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding: 10px;">สินค้า</th>
                                    <th style="text-align:left; padding: 10px;">ราคาปกติ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2" style="padding: 12px 0; color:#6b7280;">ยังไม่มีสินค้าที่เลือก</td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e5e7eb; display:flex; flex-direction:column; gap:10px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                                <div style="display:flex; align-items:center; gap:15px; width: 100%; max-width: 560px;">
                                    <span style="font-weight:300; white-space:nowrap;">รูปแบบราคา</span>
                                    <select id="sharedPriceType" name="pricing_type" style="min-width: 140px; flex: 1; max-width: 180px;">
                                        <option value="percent">%</option>
                                        <option value="fixed">ตั้งราคาเอง</option>
                                    </select>
                                    <input id="sharedPriceValue" name="pricing_value" type="number" min="0" step="0.01" value="10" style="width: 150px; flex: 1; max-width: 180px;" placeholder="10">
                                </div>
                               
                            </div>
                            <br>
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; color:#4b5563; font-size: 0.95rem;">
                                <span>ราคาก่อนลด: <strong id="campaignOriginalTotal" style="color:#111827;">฿0.00</strong></span>
                                <span>ยอดส่วนลด: <strong id="campaignDiscountTotal" style="color:#dc2626;">฿0.00</strong></span>
                                <span>ราคาหลังลด: <strong id="campaignTotal" style="font-size: 1.1rem; font-weight: 700;">฿0.00</strong></span>
                                
                            
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <a href="{{ route('campaigns.index') }}" class="btn secondary">ยกเลิก</a>
                        <button type="submit" class="btn">บันทึก</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const products = @json($products).map((product) => ({
            id: Number(product.id),
            name: product.name,
            price: Number(product.sale_price ?? product.price ?? 0),
            sku: product.sku,
        }));

        function formatPrice(value) {
            return `฿${Number(value || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function calculateDiscountedTotal(totalOriginalPrice, priceType, priceValue) {
            if (!totalOriginalPrice || totalOriginalPrice <= 0) {
                return 0;
            }

            if (priceType === 'percent') {
                const discountPercent = Math.min(Math.max(Number(priceValue || 0), 0), 100);
                return totalOriginalPrice * (1 - (discountPercent / 100));
            }

            return Number(priceValue || 0);
        }

        function getSelectedProductsOriginalTotal() {
            let total = 0;

            document.querySelectorAll('.campaign-product-table tbody tr').forEach(row => {
                const productIdInput = row.querySelector('.campaign-product-select');
                if (!productIdInput || !productIdInput.value) {
                    return;
                }

                const selectedProduct = products.find(product => String(product.id) === String(productIdInput.value));
                if (selectedProduct) {
                    total += Number(selectedProduct.price || 0);
                }
            });

            return total;
        }

        function updateRow(element) {
            const row = element.closest('tr');
            const select = row.querySelector('.campaign-product-select');
            const productId = select.value;
            const basePriceCell = row.querySelector('.price-original');
            const hiddenInput = row.querySelector('.product-special-price-input');

            if (!productId) {
                basePriceCell.textContent = '-';
                hiddenInput.value = '';
                updateCampaignTotal();
                return;
            }

            const selectedProduct = products.find(product => String(product.id) === String(productId));
            const originalPrice = selectedProduct ? Number(selectedProduct.price || 0) : 0;

            basePriceCell.textContent = formatPrice(originalPrice);
            hiddenInput.value = originalPrice.toFixed(2);

            updateCampaignTotal();
        }

        function updateCampaignTotal() {
            const totalOriginalPrice = getSelectedProductsOriginalTotal();
            const priceType = document.getElementById('sharedPriceType').value;
            const priceValue = Number(document.getElementById('sharedPriceValue').value || 0);
            const totalFinal = calculateDiscountedTotal(totalOriginalPrice, priceType, priceValue);
            const discountAmount = Math.max(totalOriginalPrice - totalFinal, 0);

            document.getElementById('campaignOriginalTotal').textContent = formatPrice(totalOriginalPrice);
            document.getElementById('campaignDiscountTotal').textContent = formatPrice(discountAmount);
            document.getElementById('campaignTotal').textContent = formatPrice(totalFinal);
        }

        function addProductRow() {
            const tbody = document.querySelector('.campaign-product-table tbody');
            const rowCount = document.querySelectorAll('.campaign-product-table tbody tr').length;
            const row = document.createElement('tr');
            const rowIndex = rowCount === 0 ? 0 : rowCount;

            row.innerHTML = `
                <td style="padding: 10px;">
                    <select class="campaign-product-select" onchange="updateRow(this)" style="width: 100%; min-width: 220px;">
                        <option value="">-- เลือกสินค้า --</option>
                        ${products.map(product => `<option value="${product.id}">${product.name} (${product.sku})</option>`).join('')}
                    </select>
                    <input type="hidden" name="products[${rowIndex}][product_id]" class="product-id-input" value="">
                    <input type="hidden" name="products[${rowIndex}][special_price]" class="product-special-price-input" value="">
                </td>
                <td class="price-original" style="padding: 10px;">-</td>
            `;

            const select = row.querySelector('.campaign-product-select');
            const hiddenProductInput = row.querySelector('.product-id-input');
            const hiddenPriceInput = row.querySelector('.product-special-price-input');

            select.addEventListener('change', function () {
                hiddenProductInput.value = this.value;
                updateRow(this);
            });

            hiddenPriceInput.value = '';

            if (tbody.querySelector('td[colspan]')) {
                tbody.innerHTML = '';
            }

            tbody.appendChild(row);
            updateCampaignTotal();
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('sharedPriceType').addEventListener('change', function () {
                document.querySelectorAll('.campaign-product-select').forEach(select => {
                    if (select.value) {
                        updateRow(select);
                    }
                });
            });

            document.getElementById('sharedPriceValue').addEventListener('input', function () {
                document.querySelectorAll('.campaign-product-select').forEach(select => {
                    if (select.value) {
                        updateRow(select);
                    }
                });
            });

            addProductRow();
        });
    </script>
</body>
</html>
