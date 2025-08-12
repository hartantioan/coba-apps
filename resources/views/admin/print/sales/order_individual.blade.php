<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Order - {{ $data->code }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h2 {
            margin-bottom: 0;
        }
        .meta {
            margin-top: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #555;
            padding: 6px;
            text-align: left;
        }
        .no-border {
            border: none;
        }
    </style>
</head>
<body>

    <h2>Sales Order</h2>
    <p class="meta"><strong>Order No:</strong> {{ $data->code }}</p>
    <p class="meta"><strong>Date:</strong>{{ date('d/m/Y',strtotime($data->post_date)) }}</p>
    <p class="meta"><strong>Customer:</strong> {{ $data->customer?->name ?? '-' }}</p>
    <p class="meta"><strong>Sales Type:</strong> {!! $data->typeSales() !!}</p>
    <p class="meta"><strong>Payment Type:</strong> {{ $data->paymentType() }}</p>
    <p class="meta"><strong>Note:</strong> {{ $data->note }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Note</th>
                <th>Grandtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->salesOrderDetail as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->item->name ?? '-' }}</td>
                    <td>{{ number_format($detail->qty, 2, ',', '.') }}</td>
                    <td>{{ number_format($detail->price, 2, ',', '.') }}</td>
                    <td>{{ number_format($detail->discount_3, 2, ',', '.') }}</td>
                    <td>{{ number_format($detail->price_after_discount, 2, ',', '.') }}</td>
                    <td>{{ $detail->note }}</td>
                    <td>{{ number_format($detail->grandtotal, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="meta" style="margin-top: 30px;"><strong>Created By:</strong> {{ $data->user->name ?? '-' }}</p>

</body>
</html>
