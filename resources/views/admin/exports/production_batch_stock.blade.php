<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th align="center">{{ __('translations.no') }}.</th>
            <th align="center">Batch Produksi</th>
            <th align="center">Post Date</th>
            <th align="center">{{ __('translations.plant') }}</th>
            <th align="center">{{ __('translations.warehouse') }}</th>
            <th align="center">{{ __('translations.code') }}</th>
            <th align="center">Nama Item</th>
            <th align="center">{{ __('translations.unit') }}</th>
            <th align="center">Area</th>
            <th align="center">Shading</th>
            <th align="center">Tanggal</th>
            <th align="center">Qty.</th>
            <th align="center">Nilai</th>
        </tr>
    </thead>
    <tbody>
        @php
            $x = 0;
        @endphp
        @foreach($data as $i => $row)
            <tr>
                <td align="center">{{ $i + 1 }}</td>
                <td align="center">{{$row['production_batch']}}</td>
                <td align="center">{{$row['post_date']}}</td>
                <td align="center">{{$row['plant']}}</td>
                <td align="center">{{$row['warehouse']}}</td>
                <td align="center">{{$row['kode']}}</td>
                <td align="center">{{$row['item']}}</td>
                <td align="center">{{$row['satuan']}}</td>
                <td align="center">{{$row['area']}}</td>
                <td align="center">{{$row['shading']}}</td>
                <td align="center">{{$row['post_date']}}</td>
                <td align="center">{{$row['cum_qty']}}</td>
                <td align="center">{{$row['cum_val']}}</td>
            </tr>
        @endforeach
      
        @if(count($data) == 0)
            <tr>
                <td colspan="14" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    
</table>