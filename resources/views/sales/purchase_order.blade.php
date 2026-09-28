<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสั่งซื้อ {{ $documentNo }}</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
    <style>
        .document-shell { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; }
        .document-header { display: flex; justify-content: space-between; gap: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 14px; margin-bottom: 14px; }
        .document-header h2 { margin: 0; color: #111827; }
        .document-header p { margin: 4px 0; color: #4b5563; font-size: 13px; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px; }
        .meta-card { border: 1px solid #f3e2d3; border-radius: 12px; padding: 10px 12px; }
        .meta-card h3 { margin: 0 0 8px; color: #9a2c00; font-size: 14px; }
        .meta-line { margin: 4px 0; color: #374151; font-size: 13px; }
        .doc-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .doc-table th, .doc-table td { border: 1px solid #e5e7eb; padding: 8px; font-size: 13px; }
        .doc-table th { background: #fff7ed; color: #9a2c00; text-align: left; }
        .doc-table td.num { text-align: right; }
        .summary-box { margin-left: auto; margin-top: 12px; width: 340px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed #e5e7eb; font-size: 13px; }
        .summary-row.total { font-size: 18px; font-weight: 700; border-top: 1px solid #e5e7eb; border-bottom: none; padding-top: 10px; margin-top: 8px; }
        .actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; }
        .signature { margin-top: 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .signature-box { text-align: center; }
        .signature-line { margin-top: 40px; border-top: 1px solid #9ca3af; padding-top: 6px; color: #6b7280; font-size: 12px; }

        @media print {
            .page { margin: 0; }
            .actions, .sidebar { display: none !important; }
            .content { padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; padding: 0 !important; }
            .document-shell { border: none !important; padding: 0 !important; }
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
                    <h1 style="margin:0;">ใบสั่งซื้อ</h1>
                    <p style="margin: 4px 0 0; color: #6b7280;">เอกสารจากข้อมูลการขายจริงในระบบ</p>
                </div>
                <a href="{{ route('sales.orders') }}" class="btn secondary">กลับหน้ารายการขาย</a>
            </div>

            <section class="document-shell">
                <header class="document-header">
                    <div>
                        <h2>{{ $company['name'] }}</h2>
                        <p>{{ $company['address'] }}</p>
                        <p>โทร {{ $company['phone'] }} | เลขผู้เสียภาษี {{ $company['tax_id'] }}</p>
                    </div>
                    <div style="text-align:right;">
                        <h2 style="color:#9a2c00;">ใบสั่งซื้อ</h2>
                        <p>เลขที่ {{ $documentNo }}</p>
                        <p>วันที่ {{ \Carbon\Carbon::parse($issueDate)->format('d/m/Y') }}</p>
                    </div>
                </header>

                <div class="meta-grid">
                    <div class="meta-card">
                        <h3>ข้อมูลลูกค้า</h3>
                        <p class="meta-line">ชื่อ: {{ $customer->name ?? '-' }}</p>
                        <p class="meta-line">โทร: {{ $customer->phone ?? '-' }}</p>
                        <p class="meta-line">อีเมล: {{ $customer->email ?? '-' }}</p>
                    </div>
                    <div class="meta-card">
                        <h3>ที่อยู่จัดส่ง</h3>
                        <p class="meta-line">{{ $shippingAddress ?: '-' }}</p>
                        <p class="meta-line">สถานะ: {{ $sale->status }}</p>
                    </div>
                </div>

                <table class="doc-table">
                    <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>รายการสินค้า</th>
                        <th style="width:90px;">จำนวน</th>
                        <th style="width:130px;">ราคา/หน่วย</th>
                        <th style="width:150px;">รวม</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $index => $item)
                        <tr>
                            <td class="num">{{ $index + 1 }}</td>
                            <td>{{ $item->campaign->name ?? $item->product->name ?? '-' }}</td>
                            <td class="num">{{ number_format((int) $item->quantity) }}</td>
                            <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="num">{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="summary-box">
                    <div class="summary-row"><span>ยอดรวม</span><strong>{{ number_format($subtotal, 2) }}</strong></div>
                    <div class="summary-row"><span>ส่วนลด</span><strong>{{ number_format($discountAmount, 2) }}</strong></div>
                    <div class="summary-row"><span>VAT {{ number_format($vatRate, 0) }}%</span><strong>{{ number_format($vatAmount, 2) }}</strong></div>
                    <div class="summary-row total"><span>ยอดสุทธิ</span><strong>{{ number_format($grandTotal, 2) }}</strong></div>
                </div>

                <div class="signature">
                    <div class="signature-box">
                        <div class="signature-line">ผู้อนุมัติ</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">ผู้รับสินค้า</div>
                    </div>
                </div>

                <div class="actions">
                    <button type="button" class="btn" onclick="window.print()">พิมพ์หน้านี้</button>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
