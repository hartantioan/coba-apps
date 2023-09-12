<table>
    <thead>
        <tr>
            <th rowspan="2" align="center">No.</th>
            <th rowspan="2" align="center">Customer</th>
            <th colspan="5" align="center">Nominal Tenggat</th>
            <th rowspan="2" align="center">Total</th>
        </tr>
        <tr>
            <th>Belum Tenggat</th>
            <th>1-30 Hari</th>
            <th>31-60 Hari</th>
            <th>61-90 Hari</th>
            <th>Diatas 90 Hari</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total0 = 0;
            $total30 = 0;
            $total60 = 0;
            $total90 = 0;
            $totalOver = 0;
            $totalAll = 0;
        @endphp
        @foreach ($data as $key => $row)
            @php
                $total0 += $row['balance0'];
                $total30 += $row['balance30'];
                $total60 += $row['balance60'];
                $total90 += $row['balance90'];
                $totalOver += $row['balanceOver'];
                $totalAll += $row['total'];
            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['balance0'] }}</td>
                <td>{{ $row['balance30'] }}</td>
                <td>{{ $row['balance60'] }}</td>
                <td>{{ $row['balance90'] }}</td>
                <td>{{ $row['balanceOver'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" align="center">Total</td>
            <td>{{ $total0 }}</td>
            <td>{{ $total30 }}</td>
            <td>{{ $total60 }}</td>
            <td>{{ $total90 }}</td>
            <td>{{ $totalOver }}</td>
            <td>{{ $totalAll }}</td>
        </tr>
    </tbody>
</table>