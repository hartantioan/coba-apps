<table class="bordered" style="font-size:10px;">
    <thead id="head_detail">
        <tr>
            <th rowspan="2">No.</th>
            <th rowspan="2">Supplier</th>
            <th colspan="5">Nominal Tenggat (Dari Tgl. Dokumen Diterima)</th>
            <th rowspan="2">Total</th>
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
        @foreach ($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row['supplier_name'] }}</td>
                <td>{{ $row['balance0'] }}</td>
                <td>{{ $row['balance30'] }}</td>
                <td>{{ $row['balance60'] }}</td>
                <td>{{ $row['balance90'] }}</td>
                <td>{{ $row['balanceOver'] }}</td>
                <td>{{ $row['total'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>