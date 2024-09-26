<table>
    <thead>
        <tr>
            <th>{{ __('translations.no') }}.</th>
            <th>No Invoice</th>
            <th>{{ __('translations.customer') }}</th>
            <th>Tgl.Post</th>
            <th>TOP</th>
            <th>Note</th>
            <th>{{ __('translations.total') }}</th>
            <th>{{ __('translations.tax') }}</th>
            <th>{{ __('translations.grandtotal') }}</th>
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
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['tax'] }}</td>
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
                <td colspan="16" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>