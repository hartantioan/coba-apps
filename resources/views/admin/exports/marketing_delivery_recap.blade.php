<table>
    <thead>
        <tr>
        <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">MOD</th>
            <th class="center-align">SO</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Expedisi</th>
            <th class="center-align">Sopir</th>
            <th class="center-align">Truk</th>
            <th class="center-align">Nopol</th>
            <th class="center-align">Item Code</th>
            <th class="center-align">Item Name</th>
            <th class="center-align">Qty (M2)</th>
           
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['mod'] }}</td>
            <td>{{ $row['so'] }}</td>
            <td>{{ $row['post_date'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['expedisi'] }}</td>
            <td>{{ $row['sopir'] }}</td>
            <td>{{ $row['truk'] }}</td>
            <td>{{ $row['nopol'] }}</td>
            <td>{{ $row['itemcode'] }}</td>
            <td>{{ $row['itemname'] }}</td>
            <td>{{ $row['qty'] }}</td>
                
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