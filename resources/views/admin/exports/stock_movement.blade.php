<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">Keterangan</th>
            <th class="center-align">Tanggal</th>
            <th class="center-align">Masuk {{$uomunit}}</th>
            <th class="center-align">Keluar {{$uomunit}}</th>
            <th class="center-align">Saldo {{$uomunit}}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="5">Saldo Sebelumnya:</td>
            <td align="center"> {{$latest}}</td>
        </tr>
        @foreach($data as $key => $row)
        <tr>
            <td align="center">{{$key+1}}</td>
            <td align="center">{{$row['keterangan']}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['masuk']}}</td>
            <td align="center">{{$row['keluar']}}</td>
            <td align="center">{{$row['final']}}</td>
        </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="5" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    
</table>