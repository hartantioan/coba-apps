<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Item Request Pembelian</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Pengguna</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Keterangan</th>
            <th class="center-align">Status</th>
            <th class="center-align">Kode Item</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">Satuan</th>
            <th class="center-align">Qty Req.</th>
            <th class="center-align">Qty PO</th>
            <th class="center-align">Tunggakan</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['user'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['qty_po'] }}</td>
                <td>{{ $row['qty_balance'] }}</td>
                
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>