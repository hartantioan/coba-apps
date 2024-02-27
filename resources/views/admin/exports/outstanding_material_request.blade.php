<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Sisa Item Request</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Status</th>
            <th class="center-align">Voider</th>
            <th class="center-align">Tgl. Void</th>
            <th class="center-align">Ket. Void</th>
            <th class="center-align">Deleter</th>
            <th class="center-align">Tgl. Delete</th>
            <th class="center-align">Ket. Delete</th>
            <th class="center-align">Pengguna</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Keterangan</th>
            <th class="center-align">Kode Item</th>
            <th class="center-align">Item</th>
            <th class="center-align">Plant</th>
            <th class="center-align">Ket. 1</th>
            <th class="center-align">Ket. 2</th>
            <th class="center-align">Qty</th>
            <th class="center-align">Satuan</th>
            <th class="center-align">Qty Pr</th>
            <th class="center-align">Satuan Pr</th>
            <th class="center-align">Qty GI</th>
            <th class="center-align">Satuan GI</th>
            <th class="center-align">Qty Sisa</th>
            <th class="center-align">Tanggal Dipakai</th>
            <th class="center-align">Gudang</th>
            <th class="center-align">Line</th>
            <th class="center-align">Mesin</th>
            <th class="center-align">Divisi</th>
            <th class="center-align">Proyek</th>
            <th class="center-align">Requester</th>
            <th class="center-align">Status Item Approval</th>
            
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['voider'] }}</td>
                <td>{{ $row['void_date'] }}</td>
                <td>{{ $row['void_note'] }}</td>
                <td>{{ $row['deleter'] }}</td>
                <td>{{ $row['delete_date'] }}</td>
                <td>{{ $row['delete_note'] }}</td>
                <td>{{ $row['user'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['plant'] }}</td>
                <td>{{ $row['ket1'] }}</td>
                <td>{{ $row['ket2'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty_pr'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty_gi'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty_balance'] }}</td>
                <td>{{ $row['required_date'] }}</td>
                <td>{{ $row['warehouse'] }}</td>
                <td>{{ $row['line'] }}</td>
                <td>{{ $row['machine'] }}</td>
                <td>{{ $row['divisi'] }}</td>
                <td>{{ $row['project'] }}</td>
                <td>{{ $row['requester'] }}</td>
                <td>{{ $row['status_item'] }}</td>
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>