<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">No.Dokumen</th>
            <th class="center-align">Tgl.Posting</th>
            <th class="center-align">Kode Supplier</th>
            <th class="center-align">Nama Supplier</th>
            <th class="center-align">Tipe</th>
            <th class="center-align">Keterangan</th>
            <th class="center-align">Total RP</th>
            <th class="center-align">Total FC</th>
            <th class="center-align">Sisa RP</th>
            <th class="center-align">Sisa FC</th>
            <th class="center-align">Based On</th>   
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td class="center-align">{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['supplier_code'] }}</td>
                <td>{{ $row['supplier_name'] }}</td>
                <td>{{ $row['type'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td class="right-align">{{ $row['total'] }}</td>
                <td class="right-align">{{ $row['total_fc'] }}</td>
                <td class="right-align">{{ $row['balance'] }}</td>
                <td class="right-align">{{ $row['balance_fc'] }}</td>
                <td class="right-align">{{ $row['references'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="12" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    <tfoot>
        <tr>
            <td colspan="11">Total</td>
            <td align="right">{{ $totalall }}</td>
        </tr>
    </tfoot>
</table>