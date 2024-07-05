@if($perlu == 1)
    <table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
        <thead>
            <tr align="center">
                <th class="center-align">{{ __('translations.no') }}.</th>
                <th class="center-align">Tanggal.</th>
                <th class="center-align">Plant.</th>
                <th class="center-align">Gudang.</th>
                <th class="center-align">Kode Item</th>
                <th class="center-align">Nama Item</th>
                <th class="center-align">{{ __('translations.unit') }}</th>
                <th class="center-align">No Dokumen</th>
                <th class="center-align">Mutasi</th>
                <th class="center-align">Balance</th>
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
                        <td align="right">{{ $row['last_qty'] }}</td>
                    </tr>
                    @php
                        $x++;
                    @endphp
                @else
                    <tr>
                        <td align="center"></td>
                        <td align="center">{{$row['date']}}</td>
                        <td align="center">{{$row['plant']}}</td>
                        <td align="center">{{$row['warehouse']}}</td>
                        <td align="center">{{$row['kode']}}</td>
                        <td align="center">{{$row['item']}}</td>
                        <td align="center">{{$row['satuan']}}</td>
                        <td align="center">{{$row['document']}}</td>
                        <td align="right">{{$row['qty']}}</td>
                        <td align="right">{{$row['cum_qty']}}</td>
                    </tr>
                    
                @endif
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
@else
    <table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
        <thead>
            <tr align="center">
                <th align="center">{{ __('translations.no') }}.</th>
                <th align="center">{{ __('translations.plant') }}</th>
                <th align="center">{{ __('translations.warehouse') }}</th>
                <th align="center">{{ __('translations.code') }}</th>
                <th align="center">Nama Item</th>
                <th align="center">{{ __('translations.unit') }}</th>
                <th align="center">Balance</th>
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