<table class="bordered" style="font-size:10px;">
    <thead>
        <tr>
            <th class="center-align">No.</th>
            <th class="center-align">Item</th>
            <th class="center-align">Stock</th>
            <th class="center-align">Plant</th>
            <th class="center-align">Gudang</th>
            <th class="center-align">Area</th>
            <th class="center-align">Shading</th>
            <th class="center-align">UOM</th>
            <th class="center-align">Lokasi</th>
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