<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">{{ __('translations.no') }}.</th>
        
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.warehouse') }}</th>
            <th class="center-align">{{ __('translations.code') }}</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">{{ __('translations.unit') }}</th>
            <th align="center">Area</th>
            <th align="center">Shading</th>
            <th align="center">Batch Produksi</th>
            <th class="center-align">{{ __('translations.qty') }}</th>
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
            <td align="center">{{$row['satuan']}}</td>
            <td align="center">{{$row['area']}}</td>
            <td align="center">{{$row['shading']}}</td>
            <td align="center">{{$row['production_batch']}}</td>
            <td align="right-align">{{$row['final']}}</td>
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