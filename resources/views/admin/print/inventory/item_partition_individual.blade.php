<!DOCTYPE html>
<html>
<head>
    <title>Item Partition</title>
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
        <h2>Item Partition</h2>
        <table class="no-border">
            <tr>
                <td><strong>Code:</strong></td>
                <td>{{ $data->code }}</td>
            </tr>
            <tr>
                <td><strong>Note:</strong></td>
                <td>{{ $data->note }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>{{ date('d/m/Y', strtotime($data->date)) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Item Partisi</th>
                <th>Qty Konversi</th>
                <th>Satuan Item Konversi</th>
                <th>Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->itemPartitionDetail as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($detail->fromStock->item)->name }}</td>
                    <td>{{ number_format($detail->qty, 2, ',', '.') }}</td>
                    <td>{{ optional($detail->fromStock->item->uomUnit)->code }}</td>
                    <td>{{ optional($detail->toStock->item)->name }}</td>
                    <td>{{ number_format($detail->qty_partition, 2, ',', '.') }}</td>
                    <td>{{ optional($detail->toStock->item->uomUnit)->code }}</td>
                    <td>{{ $detail->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
