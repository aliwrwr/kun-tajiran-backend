<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>
        /* ===== Base Reset ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* ===== Screen preview toolbar ===== */
        body {
            background: #e5e7eb;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            direction: rtl;
        }

        .screen-toolbar {
            background: #1e293b;
            color: #fff;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .screen-toolbar span { font-size: .82rem; opacity: .7; }
        .screen-toolbar .paper-btns { display: flex; gap: 6px; margin-right: auto; }
        .size-btn {
            background: #334155; color: #fff; border: none; border-radius: 5px;
            padding: 4px 12px; font-size: .78rem; cursor: pointer; transition: background .15s;
        }
        .size-btn.active, .size-btn:hover { background: #6366f1; }
        .print-btn {
            background: #10b981; color: #fff; border: none; border-radius: 5px;
            padding: 6px 18px; font-size: .85rem; font-weight: 600; cursor: pointer;
        }
        .print-btn:hover { background: #059669; }

        .receipt-wrapper {
            display: flex;
            justify-content: center;
            padding: 28px 16px 60px;
        }

        /* ===== Receipt Paper ===== */
        .receipt {
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.18);
            border-radius: 4px;
            padding: 0;
            width: var(--paper-width, 80mm);
            font-size: var(--font-size, 12px);
            color: #111;
            position: relative;
            overflow: hidden;
        }

        /* Tear-edge top */
        .receipt::before {
            content: '';
            display: block;
            height: 8px;
            background: repeating-linear-gradient(90deg, #fff 0 6px, #e5e7eb 6px 8px);
        }

        /* ===== Sections ===== */
        .r-section { padding: 8px 10px; }
        .r-divider  { border: none; border-top: 1px dashed #aaa; margin: 4px 10px; }
        .r-divider-solid { border: none; border-top: 2px solid #111; margin: 4px 10px; }

        /* Header */
        .r-header { text-align: center; padding: 10px 10px 6px; }
        .r-logo { font-size: 1.3em; font-weight: 900; letter-spacing: -0.5px; }
        .r-logo span { color: #6366f1; }
        .r-tagline { font-size: .75em; color: #666; margin-top: 2px; }
        .r-number { font-size: 1em; font-weight: 700; margin-top: 6px; }
        .r-date { font-size: .78em; color: #555; margin-top: 2px; }

        /* Status badge */
        .r-status {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: .78em;
            font-weight: 700;
            margin-top: 5px;
            border: 1.5px solid currentColor;
        }
        .status-new,.status-confirmed { color: #2563eb; }
        .status-preparing,.status-out_for_delivery { color: #d97706; }
        .status-delivered { color: #16a34a; }
        .status-rejected,.status-returned { color: #dc2626; }

        /* Section label */
        .r-label {
            font-size: .72em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #666;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        /* Customer info rows */
        .r-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: .92em; }
        .r-row .r-key { color: #555; flex-shrink: 0; margin-left: 6px; }
        .r-row .r-val { font-weight: 600; text-align: left; word-break: break-word; }

        /* Address full width */
        .r-address { font-size: .88em; line-height: 1.4; color: #333; margin-top: 3px; }

        /* Items table */
        .r-items { width: 100%; border-collapse: collapse; }
        .r-items th {
            font-size: .72em; font-weight: 700; color: #555;
            border-bottom: 1px solid #ddd; padding: 3px 4px; text-align: right;
        }
        .r-items td { padding: 4px 4px; font-size: .88em; border-bottom: 1px dotted #eee; vertical-align: top; }
        .r-items .item-name { font-weight: 600; line-height: 1.3; }
        .r-items .item-qty  { text-align: center; color: #555; white-space: nowrap; }
        .r-items .item-price { text-align: left; font-weight: 700; white-space: nowrap; }

        /* Totals */
        .r-total-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: .92em; }
        .r-total-row.final {
            font-size: 1.1em; font-weight: 900;
            border-top: 2px solid #111;
            padding-top: 5px; margin-top: 4px;
        }
        .r-total-row .amt { font-weight: 700; }
        .r-total-row.final .amt { font-size: 1.05em; }

        /* Notes */
        .r-notes { font-size: .82em; color: #555; line-height: 1.5; font-style: italic; }

        /* Footer */
        .r-footer { text-align: center; padding: 8px 10px 12px; }
        .r-footer p { font-size: .78em; color: #666; margin-bottom: 2px; }
        .r-footer .thanks { font-size: .95em; font-weight: 700; color: #111; }
        .r-barcode {
            font-family: 'Courier New', monospace;
            font-size: .65em;
            letter-spacing: 2px;
            color: #888;
            margin-top: 5px;
            display: block;
        }

        /* Tear-edge bottom */
        .receipt::after {
            content: '';
            display: block;
            height: 8px;
            background: repeating-linear-gradient(90deg, #fff 0 6px, #e5e7eb 6px 8px);
        }

        /* ===== Print styles ===== */
        @media print {
            body { background: none; }
            .screen-toolbar { display: none !important; }
            .receipt-wrapper { padding: 0; display: block; }
            .receipt {
                width: var(--paper-width, 80mm) !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 auto;
            }
            .receipt::before, .receipt::after { display: none; }
            @page {
                margin: 2mm;
                size: var(--paper-width, 80mm) auto;
            }
        }
    </style>
</head>
<body>

{{-- == Screen toolbar (hidden on print) == --}}
<div class="screen-toolbar">
    <strong style="font-size:.9rem">🖨 معاينة الفاتورة</strong>
    <span>· اختر حجم الورق:</span>
    <div class="paper-btns">
        <button class="size-btn" onclick="setPaper('57mm','10.5px')">58mm</button>
        <button class="size-btn active" onclick="setPaper('80mm','12px')">80mm</button>
        <button class="size-btn" onclick="setPaper('100mm','13px')">100mm</button>
    </div>
    <button class="print-btn" onclick="window.print()">🖨 طباعة</button>
</div>

<div class="receipt-wrapper">
<div class="receipt" id="receipt">

    {{-- Header --}}
    <div class="r-header">
        <div class="r-logo">كن <span>تاجرا</span></div>
        <div class="r-number">{{ $order->order_number }}</div>
        <div class="r-date">{{ $order->created_at->format('Y/m/d H:i') }}</div>
        <div class="r-status status-{{ $order->status }}">{{ $order->status_label }}</div>
    </div>

    <hr class="r-divider-solid">

    {{-- Customer --}}
    <div class="r-section">
        <div class="r-label">معلومات الزبون</div>
        <div class="r-row">
            <span class="r-key">الاسم:</span>
            <span class="r-val">{{ $order->customer_name }}</span>
            <span class="r-key" style="margin-right:10px">الهاتف:</span>
            <span class="r-val">{{ $order->customer_phone }}</span>
        </div>
        <div class="r-row">
            <span class="r-key">المدينة:</span>
            <span class="r-val">{{ $order->customer_city }}</span>
            @if($order->customer_address)
            <span class="r-key" style="margin-right:10px">العنوان:</span>
            <span class="r-val">{{ $order->customer_address }}</span>
            @endif
        </div>
    </div>

    <hr class="r-divider">

    {{-- Items --}}
    <div class="r-section">
        <div class="r-label">المنتجات</div>
        <table class="r-items">
            <thead>
                <tr>
                    <th style="width:50%">المنتج</th>
                    <th style="width:15%;text-align:center">الكمية</th>
                    <th style="width:35%;text-align:left">السعر</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td class="item-name">{{ $item->product_name ?? $item->product?->name_ar ?? '—' }}</td>
                    <td class="item-qty">× {{ $item->quantity }}</td>
                    <td class="item-price">{{ number_format($item->sale_price * $item->quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <hr class="r-divider">

    {{-- Totals --}}
    <div class="r-section">
        @php $subtotal = $order->items->sum(fn($i) => $i->sale_price * $i->quantity); @endphp
        @if($order->delivery_fee > 0)
        <div class="r-total-row">
            <span>المجموع الفرعي</span>
            <span class="amt">{{ number_format($subtotal) }} د.ع</span>
        </div>
        <div class="r-total-row">
            <span>رسوم التوصيل</span>
            <span class="amt">{{ number_format($order->delivery_fee) }} د.ع</span>
        </div>
        @endif
        <div class="r-total-row final">
            <span>المجموع الكلي</span>
            <span class="amt">{{ number_format($order->total_sale_price) }} د.ع</span>
        </div>

    </div>

    @if($order->notes)
    <hr class="r-divider">
    <div class="r-section">
        <div class="r-label">ملاحظات</div>
        <div class="r-notes">{{ $order->notes }}</div>
    </div>
    @endif

    <hr class="r-divider">



</div>{{-- .receipt --}}
</div>

<script>
function setPaper(width, fontSize) {
    const r = document.getElementById('receipt');
    r.style.setProperty('--paper-width', width);
    r.style.setProperty('--font-size', fontSize);
    r.style.width = width;
    r.style.fontSize = fontSize;
    // update @page
    let style = document.getElementById('dynamic-page');
    if (!style) { style = document.createElement('style'); style.id = 'dynamic-page'; document.head.appendChild(style); }
    style.textContent = `@page { size: ${width} auto; margin: 2mm; }`;
    // active btn
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
}
// Auto-print after load
window.addEventListener('load', () => setTimeout(() => window.print(), 400));
</script>
</body>
</html>
