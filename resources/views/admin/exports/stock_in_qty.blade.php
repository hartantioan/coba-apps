<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">Tanggal</th>
            <th class="center-align">Plant</th>
            <th class="center-align">Gudang</th>
            <th class="center-align">Kode</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">Satuan</th>
            <th class="center-align">No. Dokumen</th>
            <th class="center-align">Qty</th>
            <th class="center-align">Harga </th>
            <th class="center-align">Total</th>
            <th class="center-align">Cumulative Qty.</th>
            <th class="center-align">Cumulative Value</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td align="center">{{$key+1}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['plant']}}</td>
            <td align="center">{{$row['gudang']}}</td>
            <td align="center">{{$row['kode']}}</td>
            <td align="center">{{$row['item']}}</td>
            <td align="center">{{$row['satuan']}}</td>
            <td align="center">{{$row['document']}}</td>
            <td align="right-align">{{$row['qtyfinal']}}</td>
            <td align="right-align">{{$row['final']}}</td>
            <td align="right-align">{{$row['totalfinal']}}</td>
            <td align="right-align">{{$row['cum_qty']}}</td>
            <td align="right-align">{{$row['cum_val']}}</td>
        </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="4" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    
</table>