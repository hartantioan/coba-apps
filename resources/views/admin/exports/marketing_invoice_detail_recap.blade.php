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
            <th>Total</th>


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