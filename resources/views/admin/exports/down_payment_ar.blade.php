<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Tipe</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Tgl.Tenggat</th>
            <th class="center-align">Keterangan</th>
            <th class="center-align">Subtotal</th>
            <th class="center-align">Diskon</th>
            <th class="center-align">Total</th>
            <th class="center-align">Dipakai</th>
            <th class="center-align">Memo</th>
            <th class="center-align">Sisa</th>   
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td class="center-align">{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['type'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['due_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td class="right-align">{{ $row['subtotal'] }}</td>
                <td class="right-align">{{ $row['discount'] }}</td>
                <td class="right-align">{{ $row['total'] }}</td>
                <td class="right-align">{{ $row['used'] }}</td>
                <td class="right-align">{{ $row['memo'] }}</td>
                <td class="right-align">{{ $row['balance'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="13" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    <tfoot>
        <tr>
            <td colspan="12">Total</td>
            <td align="right">{{ $totalall }}</td>
        </tr>
    </tfoot>
</table>