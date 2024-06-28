<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <tr>
            <th class="center-align">No.</th>
            <th class="center-align">{{ __('translations.item') }}</th>
            <th class="center-align">{{ __('translations.customer') }}</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.price') }}</th>
            <th class="center-align">Margin</th>
            <th class="center-align">Diskon 1</th>
            <th class="center-align">Diskon 2</th>
            <th class="center-align">Diskon 3</th>
            <th class="center-align">Final Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <td align="center">{{$key+1}}</td>
            <td align="center">{{$row['item']}}</td>
            <td align="center">{{$row['customer']}}</td>
            <td align="center">{{$row['code']}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['place']}}</td>
            <td align="right">{{$row['price']}}</td>
            <td align="right">{{$row['margin']}}</td>
            <td align="right">{{$row['disc1']}}</td>
            <td align="right">{{$row['disc2']}}</td>
            <td align="right">{{$row['disc3']}}</td>
            <td align="right">{{$row['final_price']}}</td>
        </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="11" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
    
</table>