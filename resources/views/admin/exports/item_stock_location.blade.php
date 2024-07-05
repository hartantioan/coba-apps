<table class="bordered" style="font-size:10px;">
    <thead>
        <tr>
            <th class="center-align">{{ __('translations.no') }}.</th>
            <th class="center-align">{{ __('translations.item') }}</th>
            <th class="center-align">{{ __('translations.stock') }}</th>
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.warehouse') }}</th>
            <th class="center-align">{{ __('translations.area') }}</th>
            <th class="center-align">{{ __('translations.shading') }}</th>
            <th class="center-align">UOM</th>
            <th class="center-align">{{ __('translations.location') }}</th>
        </tr>
    
    </thead>
    
    <tbody id="table_body">
        @foreach($data as $key => $row)
        <tr align="center">
            <td>{{ $key }}</td>
            <td>{{ $row['item'] }}</td>
            <td>{{ $row['stock'] }}</td>
            <td>{{ $row['plant']}}</td>
            <td>{{ $row['gudang'] ?? '' }}</td>
            <td>{{ $row['area'] }}</td>
            <td>{{ $row['shading'] ?? ''}}</td>
            <td>{{ $row['satuan'] }}</td>
            <td>{{ $row['location'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
    
</table>