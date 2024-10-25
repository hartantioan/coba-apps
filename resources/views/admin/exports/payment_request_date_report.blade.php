<table border="1">
    <thead>
        <tr>
            <th class="center-align">No.</th>
            <th class="center-align">Payment Request Code</th>
            <th class="center-align">Catatan PYR</th>
            <th class="center-align">No Purchase Invoice</th>
            <th class="center-align">Vendor</th>
            <th class="center-align">No Vendor</th>
            <th class="center-align">Tgl.Bayar</th>
            <th class="center-align">Tgl.OPYM</th>
            <th class="center-align">Status</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 0; @endphp
        @foreach ($data as $row)
        @php $no++; @endphp
            <tr>
                <td >{{ $no }}</td>
                <td >{{ $row['code'] }}</td>
                <td >{{ $row['invoice_code'] }}</td>
                <td >{{ $row['note'] }}</td>
                <td >{{ $row['vendor'] }}</td>
                <td >{{ $row['invoice_no'] }}</td>
                <td >{{ $row['pay_date'] }}</td>
                <td >{{ $row['opym_date'] }}</td>
                <td >{{ $row['status'] }}</td>

            </tr>
        @endforeach
    </tbody>
</table>

