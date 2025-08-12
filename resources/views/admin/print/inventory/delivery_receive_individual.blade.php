<!DOCTYPE html>
<html>
<head>
    <title>Delivery Receive</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { margin-bottom: 30px; }
        .header h2 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: left; }
        .no-border td { border: none; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Delivery Receive</h2>
        <table class="no-border">
            <tr>
                <td><strong>Code:</strong></td>
                <td>{{ $data->code }}</td>
            </tr>
            <tr>
                <td><strong>User:</strong></td>
                <td>{{ optional($data->user)->name }}</td>
            </tr>
            <tr>
                <td><strong>Account:</strong></td>
                <td>{{ optional($data->account)->name }}</td>
            </tr>
            <tr>
                <td><strong>Receiver Name:</strong></td>
                <td>{{ $data->receiver_name }}</td>
            </tr>
            <tr>
                <td><strong>Post Date:</strong></td>
                <td> {{ date('d/m/Y',strtotime($data->post_date)) }}</td>
            </tr>
            <tr>
                <td><strong>Delivery No:</strong></td>
                <td>{{ $data->delivery_no }}</td>
            </tr>
            <tr>
                <td><strong>Note:</strong></td>
                <td>{{ $data->note }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Note</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->deliveryReceiveDetail as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($detail->item)->name }}</td>
                    <td>{{ number_format($detail->qty, 2, ',', '.') }}</td>
                    <td>{{ number_format($detail->price, 2, ',', '.') }}</td>
                    <td>{{ $detail->note }}</td>
                    <td>{{ $detail->remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
