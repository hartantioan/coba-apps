<table>
    <thead>
        <tr>
        <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Due Date Internal</th>
            <th class="center-align">Customer</th>
            <th class="center-align">Deliver Address</th>
            <th class="center-align">Tax No</th>
            <th class="center-align">No NPWP</th>
            <th class="center-align">Nama NPWP</th>
            <th class="center-align">Alamat NPWP</th>
            <th class="center-align">Tipe Payment</th>
            <th class="center-align">Subtotal</th>
            <th class="center-align">DP</th>
            <th class="center-align">Tax</th>
            <th class="center-align">Total</th>
           
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
            <td>{{ $key + 1 }}.</td>
            <td>{{ $row['code'] }}</td>
            <td>{{ $row['post_date'] }}</td>
            <td>{{ $row['duedateinternal'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['deliveraddress'] }}</td>
            <td>{{ $row['taxno'] }}</td>
            <td>{{ $row['nonpwp'] }}</td>
            <td>{{ $row['namanpwp'] }}</td>
            <td>{{ $row['alamatnpwp'] }}</td>
            <td>{{ $row['payment'] }}</td>
            <td>{{ $row['subtotal'] }}</td>
            <td>{{ $row['dp'] }}</td>
            <td>{{ $row['tax'] }}</td>
            <td>{{ $row['total'] }}</td>
        
                
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