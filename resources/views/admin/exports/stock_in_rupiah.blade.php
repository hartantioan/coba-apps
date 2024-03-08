@if($perlu == 1)
    <table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
        <thead>
            <tr align="center">
                <th align="center">No.</th>
                <th align="center">Tanggal</th>
                <th align="center">Plant</th>
                <th align="center">Gudang</th>
                <th align="center">Kode</th>
                <th align="center">Nama Item</th>
                <th align="center">Satuan</th>
                <th align="center">No. Dokumen</th>
                <th align="center">Qty</th>
                <th align="center">Harga </th>
                <th align="center">Total</th>
                <th align="center">Cumulative Qty.</th>
                <th align="center">Cumulative Value</th>
            </tr>
        </thead>
        <tbody>
            @php
                $x = 0;
            @endphp
            @foreach($data as $i => $row)
                @if($row['perlu'] == 1)
                    <tr>
                        <td align="center">{{ $x + 1 }}</td>
                        <td align="center"></td>
                        <td align="center"></td>
                        <td align="center"></td>
                        <td align="center">{{ $row['kode'] }}</td>
                        <td align="center">{{ $row['item'] }}</td>
                        <td align="center">{{ $row['satuan'] }}</td>
                        <td align="center">Saldo Awal</td>
                        <td align="center"></td>
                        <td align="center"></td>
                        <td align="center"></td>
                        <td align="right">{{ $row['last_qty'] }}</td>
                        <td align="right">{{ $row['last_nominal'] }}</td>
                    </tr>
                    @php
                        $x++;
                    @endphp
                @else
                    <tr>
                        <td align="center"></td>
                        <td align="center">{{ $row['date'] }}</td>
                        <td align="center">{{ $row['plant'] }}</td>
                        <td align="center">{{ $row['warehouse'] }}</td>
                        <td align="center">{{ $row['kode'] }}</td>
                        <td align="center">{{ $row['item'] }}</td>
                        <td align="center">{{ $row['satuan'] }}</td>
                        <td align="center">{{ $row['document'] }}</td>
                        <td  align="right">{{ $row['qty'] }}</td>
                        <td  align="right">{{ $row['final'] }}</td>
                        <td  align="right">{{ $row['total'] }}</td>
                        <td  align="right">{{ $row['cum_qty'] }}</td>
                        <td  align="right">{{ $row['cum_val'] }}</td>
                    </tr>
                @endif
            @endforeach
          
            @if(count($data) == 0)
                <tr>
                    <td colspan="13" align="center">
                        Data tidak ditemukan
                    </td>
                </tr>
            @endif
            
        </tbody>
        
    </table>
@else
    <table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
        <thead>
            <tr align="center">
                <th align="center">No.</th>
                <th align="center">Plant</th>
                <th align="center">Gudang</th>
                <th align="center">Kode</th>
                <th align="center">Nama Item</th>
                <th align="center">Satuan</th>
                <th align="center">Cumulative Qty.</th>
                <th align="center">Cumulative Value</th>
            </tr>
        </thead>
        <tbody>            
            @foreach($data as $key => $row)
            <tr>
                <td align="center">{{$key+1}}</td>
                <td align="center">{{$row['plant']}}</td>
                <td align="center">{{$row['warehouse']}}</td>
                <td align="center">{{$row['kode']}}</td>
                <td align="center">{{$row['item']}}</td>
                <td align="center">{{$row['satuan']}}</td>
                <td align="center">{{$row['cum_qty']}}</td>
                <td align="center">{{$row['cum_val']}}</td>
            </tr>
            @endforeach
            @if(count($data) == 0)
                <tr>
                    <td colspan="13" align="center">
                        Data tidak ditemukan
                    </td>
                </tr>
            @endif
            
        </tbody>
        
    </table>
@endif