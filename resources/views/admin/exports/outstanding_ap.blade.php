<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">No Invoice</th>
            <th class="center-align">Supplier/Vendor</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Tgl.Terima</th>
            <th class="center-align">TOP(Hari)</th>
            <th class="center-align">Tgl.Jatuh Tempo</th>
            <th class="center-align">Kurs</th>
            <th class="center-align">Total</th>
            <th class="center-align">Dibayar</th>
            <th class="center-align">Sisa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td>{{ $key+1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['vendor'] }}</td>
                <td class="center-align">{{ $row['post_date'] }}</td>
                <td class="center-align">{{ $row['rec_date'] }}</td>
                <td class="center-align">{{ $row['top'] }}</td>
                <td class="center-align">{{ $row['due_date'] }}</td>
                <td class="center-align">{{ $row['kurs'] }}</td>
                <td class="right-align">{{ $row['grandtotal'] }}</td>
                <td class="center-align">{{ $row['payed'] }}</td>
                <td class="center-align">{{ $row['sisa'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="11" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>GrandTotal</td>
            <td>{{$totalall}}</td>
        </tr>
    </tfoot>
</table>