<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">No</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal Serah Terima</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Refrensi</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Scanner</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Status</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor FP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">PPN</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
    </thead>
    <tbody>
        @if(count($data) > 0)
            @foreach($data as $key => $row)
                <tr>
                    <td style="border: 1px solid black;">{{ $key + 1 }}.</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandoverDetail->documentTaxHandover->post_date ?? '-' }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandoverDetail->documentTaxHandover->code ?? '-' }}</td>
                    <td style="border: 1px solid black;">{{ $row->user->name ?? '-'}}</td>
                    <td style="border: 1px solid black;">{{ $row->status()}}</td>
                    <td style="border: 1px solid black;">{{ $row->date }}</td>
                    <td style="border: 1px solid black;">{{ $row->transaction_code.$row->replace.$row->code }}</td>
                    <td style="border: 1px solid black;">'{{ number_format($row->npwp_number, 0, '.', '') }}</td>
                    <td style="border: 1px solid black;">{{ $row->npwp_name }}</td>
                    <td style="border: 1px solid black;">{{ $row->npwp_address }}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->total,2,',','.')}}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->tax,2,',','.') }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxDetail->first()->item ?? '-' }}</td>

                </tr>
            @endforeach
        @endif
    </tbody>
</table>