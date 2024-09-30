<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th>Jenis Dok.</th>
            <th>No. Dokumen</th>
            <th>No. Invoice</th>
            <th>Tanggal</th>
            <th>Customer</th>
            <th>GrandTotal</th>
            <th>Cumulative</th>

        </tr>
    </thead>
    <tbody>

        @foreach($data as $row)

        <tr align="center">
            @if ($row->jenis=='1')
            <td>Invoice</td>
            @endif
            @if ($row->jenis=='2')
            <td>Incoming</td>
            @endif
            <td>{{ $row->CODE }}</td>
            <td>{{ $row->ref }}</td>
            <td>{{ $row->post_date }}</td>
            <td>{{ $row->customer }}</td>
            <td>{{ $row->grandtotal }}</td>
            <td>{{ $row->cumulative_sum }}</td>

        </tr>



        @endforeach
    </tbody>
</table>