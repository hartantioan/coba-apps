<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <tr>
            <th class="center-align">{{ __('translations.no') }}.</th>
            <th class="center-align">Supplier</th>
            <th class="center-align">{{ __('translations.code') }}</th>
            <th class="center-align">{{ __('translations.item') }}</th>
            <th class="center-align">TGL</th>
            <th class="center-align">Harga Awal</th>
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
            <td align="center">{{$row['supplier']}}</td>
            <td align="center">{{$row['code']}}</td>
            <td align="center">{{$row['item']}}</td>
            <td align="center">{{$row['date']}}</td>
            <td align="center">{{$row['price']}}</td>
            <td align="center">{{$row['disc1']}}</td>
            <td align="center">{{$row['disc2']}}</td>
            <td align="center">{{$row['disc3']}}</td>
            <td align="center">{{$row['totalfinal']}}</td>
        </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="19" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    
</table>