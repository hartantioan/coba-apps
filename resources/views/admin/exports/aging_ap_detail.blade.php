<table>
    <thead>
        <tr>
            <th rowspan="2" align="center">No.</th>
            <th rowspan="2" align="center">Supplier</th>
            <th rowspan="2" align="center">Invoice</th>
            <th rowspan="2" align="center">{{ __('translations.nominal') }}</th>
            <th colspan="{{ $countPeriod }}" align="center">Nominal Tenggat</th>
        </tr>
        <tr>
            @foreach($column as $row)
                <th>{{ $row['name'] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row['supplier_name'] }}</td>
                <td>{{ $row['invoice'] }}</td>
                <td align="right">{{ round($row['total'],2) }}</td>
                @foreach($row['data'] as $rowdetail)
                    <td align="right">{{ round($rowdetail['balance'],2) }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr id="text-grandtotal">
            <td align="right" colspan="3">Total</td>
            <td align="right">{{ round($totalall,2) }}</td>
            @foreach($column as $row)
                <td align="right">{{ round($row['total'],2) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>