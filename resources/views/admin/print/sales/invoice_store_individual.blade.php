<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        @page {
            /* size: 20cm 8cm portrait; */
            margin: 0.7cm 1.7cm 0.7cm 0.7cm !important;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 70mm;
            margin: 0;
            padding: 0;
        }

        .receipt {
            padding: 10px;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="title">
            {{-- {{ $storeName ?? 'My Store' }} --}}
        </div>

        <div class="line"></div>

        <p>Date: {{ now()->format('Y-m-d H:i') }}</p>

        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
            @foreach($data->invoiceDetail as $row)
                <tr>
                    <td style="width: 60%; word-wrap: break-word;">
                        {{ $row->storeItemStock->item->name }}
                    </td>
                    <td style="width: 40%; text-align: right;">
                        {{ number_format($row->qty, 0) }} x {{ number_format($row->price, 2) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 10px; font-size: 11px; color: #666;">

                    </td>
                    <td style="text-align: right; font-size: 11px; color: #666;">
                        {{ number_format($row->before_discount, 2) }}
                    </td>
                </tr>
                @if($row->discount > 0)
                <tr>
                    <td style="padding-left: 10px; font-size: 11px; color: #666;">
                        Discount:
                    </td>
                    <td style="text-align: right; font-size: 11px; color: #666;">
                        -{{ number_format($row->discount, 2) }}
                    </td>
                </tr>
                @endif
            @endforeach
        </table>


        <div class="line"></div>

        <div class="item">
            <strong>Total</strong>
            <strong>{{ number_format( $data->grandtotal , 2) }}</strong>
        </div>

        <div class="footer">
            Thank you for your purchase!
        </div>
    </div>
</body>
</html>
