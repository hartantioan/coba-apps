<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Outstanding SO</th>
        </tr>
        <tr>
            <th class="center-align">No</th>

            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Tipe</th>
            <th class="center-align">PO</th>
            <th class="center-align">Pengiriman</th>
            <th class="center-align">Truck</th>
            <th class="center-align">Tipe Pembayaran</th>
            <th class="center-align">Alamat Kirim</th>
            <th class="center-align">Provinsi</th>
            <th class="center-align">Kota</th>
            <th class="center-align">Kecamatan</th>
            <th class="center-align">Note Internal</th>
            <th class="center-align">Note External</th>
            <th class="center-align">Item Code</th>
            <th class="center-align">Item Name</th>
            <th class="center-align">Qty SO (M2)</th>
            <th class="center-align">Price</th>
            <th class="center-align">Disc 1</th>
            <th class="center-align">Disc 2</th>
            <th class="center-align">Disc 3</th>
            <th class="center-align">Sisa Qty (M2)</th>


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
            <td>{{ $row['tipe'] }}</td>
            <td>{{ $row['po'] }}</td>
             
            <td>{{ $row['pengiriman'] }}</td>
            <td>{{ $row['truck'] }}</td>
            <td>{{ $row['pembayaran'] }}</td>
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
            <td>{{ $row['qtymod'] }}</td>

        </tr>
        @endforeach

        @endif
    </tbody>
</table>