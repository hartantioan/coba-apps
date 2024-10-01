<table>
    <thead>
        <tr>
           
            <th align="center">Code</th>
            <th align="center">Tanggal</th>
            <th align="center">Jatuh Tempo</th>
            <th align="center">Kode Cust</th>
            <th align="center">Customer</th>
            <th align="center">Usia Invoice</th> 
            <th align="center">Usia Jatuh Tempo</th>
            <th align="center">Belum Jatuh Tempo</th>
            <th align="center">Jatuh Tempo</th>
            <th align="center">Total</th>
            <th align="center">Sisa</th>
            <th align="center">0-30</th>
            <th align="center">31-60</th>
            <th align="center">61-90</th>
            <th align="center">91-120</th>
            <th align="center">>120</th>
        </tr>
        
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            <tr>
                <td>{{ $row->code }}</td>
                <td>{{ $row->post_date }}</td>
                <td>{{ $row->due_date_internal }}</td>
                <td>{{ $row->kodecust }}</td>
                <td>{{ $row->customer }}</td>
                <td>{{ $row->usiainvoice }}</td>
                <td>{{ $row->usiaoverdue }}</td>
                <td>{{ $row->BJT }}</td>
                <td>{{ $row->JT }}</td>
                <td>{{ $row->grandtotal }}</td>
                <td>{{ $row->sisa }}</td>
                <td>{{ $row->a }}</td>
                <td>{{ $row->b }}</td>
                <td>{{ $row->c }}</td>
                <td>{{ $row->d }}</td>
                <td>{{ $row->e }}</td>
            </tr>
        @endforeach
     
    </tbody>
</table>