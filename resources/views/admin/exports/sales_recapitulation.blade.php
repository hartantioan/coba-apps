<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th>No.</th>
            <th>No Invoice</th>
            <th>Customer</th>
            <th>Tgl.Post</th>
            <th>TOP</th>
            <th>Note</th>
            <th>Subtotal</th>
            <th>Discount</th>
            <th>Total</th>
            <th>PPN</th>
            <th>Total+PPN</th>
            <th>Pembulatan</th>
            <th>Grandtotal</th>
            <th>Terjadwal</th>
            <th>Terkirim</th>
            <th>Retur</th>
            <th>Invoice</th>
            <th>Memo</th>
            <th>Dibayar</th>
            <th>Sisa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['top'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['subtotal'] }}</td>
                <td>{{ $row['discount'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['tax'] }}</td>
                <td>{{ $row['total_after_tax'] }}</td>
                <td>{{ $row['rounding'] }}</td>
                <td>{{ $row['grandtotal'] }}</td>
                <td>{{ $row['schedule'] }}</td>
                <td>{{ $row['sent'] }}</td>
                <td>{{ $row['return'] }}</td>
                <td>{{ $row['invoice'] }}</td>
                <td>{{ $row['memo'] }}</td>
                <td>{{ $row['payment'] }}</td>
                <td>{{ $row['balance'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="20" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>