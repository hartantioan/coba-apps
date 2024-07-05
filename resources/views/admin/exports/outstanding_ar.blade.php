<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>{{ __('translations.no') }}.</th>
            <th>No Invoice</th>
            <th>{{ __('translations.customer') }}</th>
            <th>TGL Post</th>
            <th>TOP(Hari)</th>
            <th>Nama Item</th>
            <th>Note</th>
            <th>Qty Order</th>
            <th>Qty Invoice</th>
            <th>{{ __('translations.unit') }}</th>
            <th>Harga Satuan</th>
            <th>{{ __('translations.total') }}</th>
            <th>{{ __('translations.tax') }}</th>
            <th>Total Stl PPN</th>
            <th>Pembulatan</th>
            <th>{{ __('translations.grandtotal') }}</th>
            <th>Memo</th>
            <th>Dibayar</th>
            <th>Sisa</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['top'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['qty_order'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td>{{ $row['price'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['tax'] }}</td>
                <td>{{ $row['total_after_tax'] }}</td>
                <td>{{ $row['rounding'] }}</td>
                <td>{{ $row['grandtotal'] }}</td>
                <td>{{ $row['memo'] }}</td>
                <td>{{ $row['payment'] }}</td>
                <td>{{ $row['balance'] }}</td>
            </tr>
        @endforeach
            <tr>
                <td colspan="18" align="right">Grandtotal</td>
                <td>{{ $grandtotal }}</td>
            </tr>
        @endif
        @if(count($data) == 0)
            <tr>
                <td colspan="19" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>