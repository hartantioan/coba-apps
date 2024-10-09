<table>
    <thead>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Status</th>
            <th class="center-align">SO</th>
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
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Tipe Perhitungan Expedisi</th>
            <th class="center-align">Pengiriman</th>
            <th class="center-align">Alamat Kirim</th>
            <th class="center-align">Kota</th>
            <th class="center-align">Kecamatan</th>
            <th class="center-align">Truck</th>
            <th class="center-align">Status Siap Kirim</th>
            <th class="center-align">Tgl.Kirim</th>
            <th class="center-align">Note Internal</th>
            <th class="center-align">Note External</th>
            <th class="center-align">Item Code</th>
            <th class="center-align">Item Name</th>
            <th class="center-align">Qty Konversi</th>
            <th class="center-align">Satuan Konversi</th>
            <th class="center-align">Qty MOD (M2)</th>
            <th class="center-align">Note Item</th>
            <th class="center-align">SJ</th>
            <th class="center-align">Timbangan</th>

        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['status'] }}</td>
            <td>{{ $row['so'] }}</td>
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
            <td>{{ $row['expedisi'] }}</td>
            <td>{{ $row['pengiriman'] }}</td>
            <td>{{ $row['alamatkirim'] }}</td>
            <td>{{ $row['kota'] }}</td>
            <td>{{ $row['kecamatan'] }}</td>
            <td>{{ $row['truk'] }}</td>
            <td>{{ $row['statuskirim'] }}</td>
            <td>{{ $row['delivery_date'] }}</td>
            <td>{{ $row['noteinternal'] }}</td>
            <td>{{ $row['noteexternal'] }}</td>
            <td>{{ $row['itemcode'] }}</td>
            <td>{{ $row['itemname'] }}</td>
            <td>{{ $row['qty_conversion'] }}</td>
            <td>{{ $row['unit_conversion'] }}</td>
            <td>{{ $row['qty']*$row['konversi'] }}</td>
            @if ($row['noteitem'] == 'null')
            <td></td>
            @else
            <td>{{$row['noteitem']}}</td>
            @endif
            <td>{{ $row['sj'] }}</td>
            <td>{{ $row['timbangan'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="17" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>
