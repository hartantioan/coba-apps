<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">No</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal Serah Terima</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Refrensi</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Scanner</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">{{ __('translations.status') }}</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor FP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">{{ __('translations.tax') }}</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
            @foreach($data as $key => $row)
                <tr>
                    <td style="border: 1px solid black;">{{ $key + 1 }}.</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->documentTaxHandoverDetail->documentTaxHandover->post_date ?? '-' }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->documentTaxHandoverDetail->documentTaxHandover->code ?? '-' }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->user->name ?? '-'}}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->status() }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->date }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->transaction_code.$row->documentTax->replace.$row->documentTax->code }}</td>
                    <td style="border: 1px solid black;">'{{$row->documentTax->npwp_number}}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_name }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_address }}</td>
                    <td style="border: 1px solid black;">{{ number_format(round($row->total - 0.5, 0, PHP_ROUND_HALF_UP),2,',','.')}}</td>
                    <td style="border: 1px solid black;">{{ number_format(round($row->tax - 0.5, 0, PHP_ROUND_HALF_UP),2,',','.') }}</td>
                    <td style="border: 1px solid black;">{{ $row->item}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>