<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Order #{{ $order->order_number }}</title>
    <style>
        /* CSS KHUSUS PRINTER THERMAL 58mm */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            width: 100%;
            max-width: 46mm;
            margin: 0;
            padding: 0;
            text-align: left;
            line-height: 1.2;
            background-color: white;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 2mm; }
        .mb-2 { margin-bottom: 4mm; }
        .mt-1 { margin-top: 2mm; }
        .mt-2 { margin-top: 4mm; }
        .border-dashed {
            border-bottom: 1px dashed #000;
            margin-bottom: 2mm;
            padding-bottom: 2mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 1px 0;
        }
        .col-qty { width: 15%; }
        .col-price { width: 35%; text-align: right; }
        
        .header h1 {
            font-size: 16px;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
        }
        
        .meta-info {
            font-size: 11px;
        }

        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print(); setTimeout(function(){ window.close(); }, 500);">

    <div class="header text-center border-dashed">
        <h1>Z COFFEE</h1>
        <p>CV Sintesa Mandiri Karya Pasir Biru, Kec. Cibiru, Kota Bandung</p>
        <p>Telp: 08123456789</p>
    </div>

    <div class="meta-info border-dashed">
        <table>
            <tr>
                <td>Tgl</td>
                <td>: {{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Order</td>
                <td>: {{ $order->order_number }}</td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td>: {{ ($order->order_type ?? 'dine_in') === 'take_away' ? 'TAKE AWAY' : 'DINE IN - Meja '.$order->table_number }}</td>
            </tr>
        </table>
    </div>

    <div class="border-dashed">
        <table>
            @foreach($order->items as $item)
            <tr>
                <td colspan="3" class="font-bold">{{ $item->menu_name }}</td>
            </tr>
            <tr>
                <td class="col-qty">{{ $item->quantity }}x</td>
                <td>
                    @if($item->has_sugar || $item->has_serve || $item->notes)
                        <div style="font-size: 10px;">
                            @if($item->has_sugar) {{ $item->sugar_label }} <br> @endif
                            @if($item->has_serve) {{ $item->serve_label }} <br> @endif
                            @if($item->notes) Catatan: {{ $item->notes }} @endif
                        </div>
                    @endif
                </td>
                <td class="col-price">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="border-dashed">
        <table>
            <tr class="font-bold">
                <td>TOTAL</td>
                <td class="text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>METODE</td>
                <td class="text-right uppercase">{{ $order->payment_method === 'qris' ? 'QRIS' : 'Cash' }}</td>
            </tr>
        </table>
    </div>

    @if($order->customer_note)
    <div class="border-dashed" style="font-size: 11px;">
        <span class="font-bold">Nama / Catatan Order:</span><br>
        {{ $order->customer_note }}
    </div>
    @endif

    <div class="text-center mt-2" style="font-size: 11px;">
        <p class="mb-1">Terima Kasih<br>Silakan Datang Kembali!</p>
        <p style="font-size: 9px;">Powered by ZCoffee POS</p>
    </div>

</body>
</html>
