<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">{{ __('translations.no') }}.</th>
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.warehouse') }}</th>
            <th class="center-align">Kode Item</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">Tgl Terakhir</th>
            <th class="center-align">Lama Hari</th> 
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td align="center">{{$key+1}}</td>
            <td align="center">{{$row['plant']}}</td>
            <td align="center">{{$row['gudang']}}</td>
            <td align="center">{{$row['kode']}}</td>
            <td align="center">{{$row['item']}}</td>
            <td align="center">{{$row['keterangan']}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['lamahari']}}</td>
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