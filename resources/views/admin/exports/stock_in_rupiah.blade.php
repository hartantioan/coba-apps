<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">Item</th>
            <th class="center-align">date</th>
            <th class="center-align">Harga Pokok Penjualan</th>
            <th class="center-align">Qty Final</th>
            <th class="center-align">Total Harga Final</th> 
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td align="center">{{$key+1}}</td>
            <td align="center">{{$row['item']}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['final']}}</td>
            <td align="center">{{$row['qtyfinal']}}</td>
            <td align="center">{{$row['totalfinal']}}</td>
        </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="6" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    
</table>