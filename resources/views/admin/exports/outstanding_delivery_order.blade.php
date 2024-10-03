<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Outstanding SJ</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Dokumen</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl Void</th>
            <th>Ket Void</th>
            <th>Deleter</th>
            <th>Tgl Delete</th>
            <th>Ket Delete</th>
            <th>Doner</th>
            <th>Tgl Done</th>
            <th>Ket Done</th>
            <th>NIK</th>
            <th>User</th>
            <th>Tgl.Post</th>
            <th>Customer</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Plant</th>
            <th>Qty Delivery</th>
            {{-- <th>Konversi</th> --}}
            <th>Satuan</th>
            <th>Qty (M2)</th>
            <th>Satuan</th>
            <th>Gudang</th>
            <th>Area</th>
            <th>Shading</th>
            <th>Batch</th>
            <th>Tipe Pengiriman</th>
            <th>Expedisi</th>
            <th>Sopir</th>
            <th>No WA Sopir</th>
            <th>Truk</th>
            <th>Nopol</th>
            <th>Outlet</th>
            <th>Alamat Tujuan</th>
            <th>Catatan Internal</th>
            <th>Catatan Eksternal</th>
            <th>Tracking</th>
            <th>Barang dikirimkan</th>
            <th>Barang diterima customer</th>
            <th>SJ Kembali</th>
            <th>Based On</th>
            <th>SO</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
        <tr>
            <td>{{ $row['no'] }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['voider'] }}</td>
                <td>{{ $row['tgl_void'] }}</td>
                <td>{{ $row['ket_void'] }}</td>
                <td>{{ $row['deleter'] }}</td>
                <td>{{ $row['tgl_delete'] }}</td>
                <td>{{ $row['ket_delete'] }}</td>
                <td>{{ $row['doner'] }}</td>
                <td>{{ $row['tgl_done'] }}</td>
                <td>{{ $row['ket_done'] }}</td>
                <td>{{ $row['nik'] }}</td>
                <td>{{ $row['user'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['itemcode'] }}</td>
                <td>{{ $row['itemname'] }}</td>
                <td>{{ $row['plant'] }}</td>
                <td>{{ $row['qtysj'] }}</td>
                {{-- <td>{{ $row['qty_konversi'] }}</td> --}}
                <td>{{ $row['satuan_konversi'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['gudang'] }}</td>
                <td>{{ $row['area'] }}</td>
                <td>{{ $row['shading'] }}</td>
                <td>{{ $row['batch'] }}</td>
                <td>{{ $row['type_delivery'] }}</td>
                <td>{{ $row['expedisi'] }}</td>
                <td>{{ $row['sopir'] }}</td>
                <td>{{ $row['no_wa_supir'] }}</td>
                <td>{{ $row['truk'] }}</td>
                <td>{{ $row['nopol'] }}</td>
                <td>{{ $row['outlet'] }}</td>
                <td>{{ $row['alamat_tujuan'] }}</td>
                <td>{{ $row['catatan_internal'] }}</td>
                <td>{{ $row['catatan_eksternal'] }}</td>
                <td>{{ $row['tracking'] }}</td>
                <td>{{ $row['status_item_sent'] }}</td>
                <td>{{ $row['status_received_by_customer'] }}</td>
                <td>{{ $row['status_returned_document'] }}</td>
                <td>{{ $row['based_on'] }}</td>
                <td>{{ $row['so'] }}</td>
        </tr>
        @endforeach

        @endif
    </tbody>
</table>