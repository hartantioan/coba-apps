<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Outstanding AR Invoice</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Due Date Internal</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Deliver Address</th>
            <th class="center-align">Tax No</th>
            <th class="center-align">Tipe Payment</th>
            <th class="center-align">Total</th>
            <th class="center-align">Aging (Days)</th>
           
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
        <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['post_date'] }}</td>
            <td>{{ $row['duedateinternal'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['deliveraddress'] }}</td>
            <td>{{ $row['taxno'] }}</td>
            <td>{{ $row['payment'] }}</td>
            <td>{{ $row['total'] }}</td>
            <td>{{ $row['aging'] }}</td>
        
        </tr>
        @endforeach

        @endif
    </tbody>
</table>