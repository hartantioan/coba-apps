<!DOCTYPE html>
<html>
<head>
    <title>Inventory Issue</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .header { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .no-border td { border: none; }
        .center-align { text-align: center; }
        .right-align { text-align: right; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Pengeluaran Barang</h2>
        <table class="no-border">
            <tr>
                <td><strong>Code:</strong></td>
                <td>{{ $data->code }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td>{{ date('d/m/Y', strtotime($data->date)) }}</td>
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
                <th class="center-align">No.</th>
                <th class="center-align">Item</th>
                <th class="center-align">Qty</th>
                <th class="center-align">Satuan</th>
                <th class="center-align">Item Toko</th>
                <th class="center-align">Stock di Toko Saat Ini</th>
                <th class="center-align">Qty Konversi</th>
                <th class="center-align">Satuan Item Konversi</th>
                <th class="center-align">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->InventoryIssueDetail as $i => $row)
                <tr>
                    <td class="center-align">{{ $i + 1 }}</td>
                    <td>{{ $row->itemStockNew->item->code }} - {{ $row->itemStockNew->item->name }}</td>
                    <td class="right-align">{{ number_format($row->qty, 2, ',', '.') }}</td>
                    <td class="center-align">{{ $row->itemStockNew->item->uomUnit->code }}</td>
                    <td>{{ $row->storeItemStock->item->code }} - {{ $row->storeItemStock->item->name }}</td>
                    <td class="right-align">{{ number_format($row->storeItemStock->qty, 2, ',', '.') }}</td>
                    <td class="right-align">{{ number_format($row->qty_store_item, 2, ',', '.') }}</td>
                    <td class="center-align">{{ $row->storeItemStock->item->uomUnit->code }}</td>
                    <td>{{ $row->note }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
