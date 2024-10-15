<table>
    <thead>
        <tr>
            <th class="center-align">No Invoice</th>
            <th>Tanggal Invoice</th>
            <th>Tanggal Due Date</th>
            <th class="center-align">No SJ</th>
            <th>Tanggal SJ</th>
            <th class="center-align">No MOD</th>
            <th>PO Cust.</th>
            <th>Customer</th>
            <th>Tipe Penjualan</th>
            <th>Jenis Pengiriman</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Uom</th>
            <th>Subtotal</th>
            <th>Total</th>
            <th>Tax</th>
            <th>Grand Total</th>
            <th>Bayar</th>
            <th>Sisa</th>
            <th>Status</th>

        </tr>
    </thead>
    <tbody>

        @foreach($data as $key => $row)
        <tr>

            <td>{{ $row['code'] }}</td>
            <td>{{ $row['tglinvoice'] }}</td>
            <td>{{ $row['tglduedate'] }}</td>
            <td>{{ $row['nosj'] }}</td>
            <td>{{ $row['tglsj'] }}</td>
            <td>{{ $row['nomod'] }}</td>

            <td>{{ $row['pocust'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['typesell'] }}</td>
            <td>{{ $row['type'] }}</td>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['qty'] }}</td>
            <td>{{ $row['uom'] }}</td>
            <td>{{ $row['grandtotal'] }}</td>
            @if($row['code']!='')
            @if($row['row']>1 && $row['checkdata']==1)
            <td rowspan="{{$row['row']}}">{{ $row['totalinvoice'] }}</td>
            <td rowspan="{{$row['row']}}">{{ $row['tax'] }}</td>
            <td rowspan="{{$row['row']}}">{{ $row['grandtotalinvoice'] }}</td>
            <td rowspan="{{$row['row']}}">{{ $row['totalbayar'] }}</td>
            <td rowspan="{{$row['row']}}">{{ $row['grandtotalinvoice']-$row['totalbayar'] }}</td>
            @if($row['grandtotalinvoice']-$row['totalbayar']==0)
            <td rowspan="{{$row['row']}}">Lunas</td>
            @else
            <td rowspan="{{$row['row']}}">Outstanding</td>
            @endif
            @elseif ($row['checkdata']==2)
            @else
            <td>{{ $row['totalinvoice'] }}</td>
            <td>{{ $row['tax'] }}</td>
            <td>{{ $row['grandtotalinvoice'] }}</td>
            <td>{{ $row['totalbayar'] }}</td>
            <td>{{ $row['grandtotalinvoice']-$row['totalbayar'] }}</td>
            @if($row['grandtotalinvoice']-$row['totalbayar']==0)
            <td>Lunas</td>
            @else
            <td>Outstanding</td>
            @endif
            @endif
            @endif

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