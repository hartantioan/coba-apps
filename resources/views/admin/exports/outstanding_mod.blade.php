<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Outstanding MOD</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">No SO</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Tipe Perhitungan Expedisi</th>
            <th class="center-align">Pengiriman</th>
            <th class="center-align">Alamat Kirim</th>
            <th class="center-align">Kota</th>
            <th class="center-align">Kecamatan</th>
            <th class="center-align">Truck</th>
            <th class="center-align">Status Siap Kirim</th>
            <th class="center-align">Note Internal</th>
            <th class="center-align">Note External</th>
            <th class="center-align">Item Code</th>
            <th class="center-align">Item Name</th>
            <th class="center-align">Qty MOD (M2)</th>
            <th class="center-align">Note Item</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
        @if ($row['nosj']=='')
        <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['noso'] }}</td>
            <td>{{ $row['post_date'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['expedisi'] }}</td>
            <td>{{ $row['pengiriman'] }}</td>
            <td>{{ $row['alamatkirim'] }}</td>
            <td>{{ $row['kota'] }}</td>
            <td>{{ $row['kecamatan'] }}</td>
            <td>{{ $row['truk'] }}</td>
            <td>{{ $row['statuskirim'] }}</td>
            <td>{{ $row['noteinternal'] }}</td>
            <td>{{ $row['noteexternal'] }}</td>
            <td>{{ $row['itemcode'] }}</td>
            <td>{{ $row['itemname'] }}</td>
            <td>{{ $row['qty']*$row['konversi'] }}</td>
            @if ($row['noteitem'] == 'null')
            <td></td>
            @else
            <td>{{$row['noteitem']}}</td>
            @endif
        </tr>
        @endif
        @endforeach

        @endif
    </tbody>
</table>