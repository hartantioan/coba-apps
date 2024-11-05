<table>
    <thead>
        <tr>
            <th rowspan="2" align="center">{{ __('translations.no') }}.</th>
            <th rowspan="2" align="center">Code</th>
            <th rowspan="2" align="center">{{ __('translations.customer') }}</th>
            <th rowspan="2" align="center">Grup</th>
            <th rowspan="2" align="center">Credit Limit</th>
            <th rowspan="2" align="center">Sisa Limit</th>
            <th rowspan="2" align="center">Outstand BG</th>
            <th rowspan="2" align="center">Total Tagihan</th>
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
                <td>{{ $row['customer_code'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['customer_group'] }}</td>
                <td align="right">{{ round($row['limit_credit'],2) }}</td>
                <td align="right">{{ round($row['credit_balance'],2) }}</td>
               
                <td align="right">{{ round($row['outstand_check'],2) }}</td>
                <td align="right">{{ round($row['total'],2) }}</td>
                @foreach($row['data'] as $rowdetail)
                    <td align="right">{{ round($rowdetail['balance'],2) }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr id="text-grandtotal">
            <td align="right" colspan="7">Total</td>
            <td align="right">{{ round($totalall,2) }}</td>
            @foreach($column as $row)
                <td align="right">{{ round($row['total'],2) }}</td>
            @endforeach
        </tr>
    </tbody>
</table>