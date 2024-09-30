<table>
    <thead>
        <tr>
            <th>{{ __('translations.no') }}.</th>
            <th>No Dokumen</th>
            <th>Customer</th>
            <th>Tgl.Post</th>
            <th>Total</th>
            <th>Tax</th>
            <th>Grand Total</th>
            <th>Tax No</th>
            <th>No Incoming</th>
            <th>Tgl Incoming</th>
            <th>Note</th>
            <th>Status</th>
        
           
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['total'] }}</td>
                <td>{{ $row['tax'] }}</td>
                <td>{{ $row['grandtotal'] }}</td>
                <td>{{ $row['taxno'] }}</td>
                <td>{{ $row['noincoming'] }}</td>
                <td>{{ $row['tglincoming'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['status'] }}</td>
              
              
                
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="16" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
    </tbody>
</table>