<table>
    <thead>
        <tr>
            <th>{{ __('translations.no') }}.</th>
            <th>Status</th>
            <th>No Dokumen</th>
            <th>Voider</th>
            <th>Tgl. Void</th>
            <th>Ket. Void</th>
            <th>Deleter</th>
            <th>Tgl. Delete</th>
            <th>Ket. Delete</th>
            <th>Doner</th>
            <th>Tgl. Done</th>
            <th>Ket. Done</th>
            <th>NIK</th>
            <th>Pengguna</th>
            <th>Tgl.Post</th>
            <th>Customer</th>
            <th>TOP</th>
            <th>Tipe</th>
            <th>PO Cust</th>
            <th>Pengiriman</th>
            <th>Pembayaran</th>
            <th>Truck</th>
            <th>Alamat Kirim</th>
            <th>Provinsi</th>
            <th>Kota</th>
            <th>Kecamatan</th>
            <th>Note Internal</th>
            <th>Note External</th>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Disc 1</th>
            <th>Disc 2</th>
            <th>Disc 3</th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['voider'] }}</td>
                <td>{{ $row['void_date'] }}</td>
                <td>{{ $row['void_note'] }}</td>
                <td>{{ $row['deleter'] }}</td>
                <td>{{ $row['delete_date'] }}</td>
                <td>{{ $row['delete_note'] }}</td>
                <td>{{ $row['doner'] }}</td>
                <td>{{ $row['done_date'] }}</td>
                <td>{{ $row['done_note'] }}</td>
                <td>{{ $row['nik'] }}</td>
                <td>{{ $row['user'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['top'] }}</td>
                <td>{{ $row['tipe'] }}</td>
                <td>{{ $row['po'] }}</td>
                <td>{{ $row['pengiriman'] }}</td>
                <td>{{ $row['pembayaran'] }}</td>
                <td>{{ $row['truck'] }}</td>
                <td>{{ $row['alamatkirim'] }}</td>
                <td>{{ $row['provinsi'] }}</td>
                <td>{{ $row['kota'] }}</td>
                <td>{{ $row['kecamatan'] }}</td>
                <td>{{ $row['noteinternal'] }}</td>
                <td>{{ $row['noteexternal'] }}</td>
                <td>{{ $row['itemcode'] }}</td>
                <td>{{ $row['itemname'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['price'] }}</td>
                <td>{{ $row['disc1'] }}</td>
                <td>{{ $row['disc2'] }}</td>
                <td>{{ $row['disc3'] }}</td>


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
