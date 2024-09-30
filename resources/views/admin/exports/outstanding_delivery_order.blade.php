<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Outstanding SJ</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Sopir</th>
            <th class="center-align">Truk</th>
            <th class="center-align">Nopol</th>
            <th class="center-align">Item Code</th>
            <th class="center-align">Item Name</th>
            <th class="center-align">Qty (M2)</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
        <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['post_date'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['sopir'] }}</td>
            <td>{{ $row['truk'] }}</td>
            <td>{{ $row['nopol'] }}</td>
            <td>{{ $row['itemcode'] }}</td>
            <td>{{ $row['itemname'] }}</td>
            <td>{{ $row['qty'] }}</td>
        </tr>
        @endforeach

        @endif
    </tbody>
</table>