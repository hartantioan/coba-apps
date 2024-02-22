<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center" rowspan="2"  style="background-color: navy; color: white;border: 1px solid white;">No</th>
            <th align="center" colspan="2" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak</th>
            <th align="center" colspan="2" style="background-color: navy; color: white;border: 1px solid white;">Faktur Pajak yang Diganti/Diretur</th>
            <th align="center" colspan="3" style="background-color: navy; color: white;border: 1px solid white;">Supplier</th>
            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">DPP</th>
            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">PPN</th>
            <th align="center" rowspan="2" style="background-color: navy; color: white;border: 1px solid white;">Nama Barang</th>
        </tr>
        <tr>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nomor</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">NPWP</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama </th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Alamat Lengkap</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
            @foreach($data as $key => $row)
                <tr>
                    <td style="border: 1px solid black;">{{ $key + 1 }}.</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->date }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->code }}</td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;"></td>
                    <td style="border: 1px solid black;">'{{ number_format($row->documentTax->npwp_number, 0, '.', '') }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_name }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_address }}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->total,3,',','.')}}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->tax,3,',','.') }}</td>
                    <td style="border: 1px solid black;">{{ $row->item}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>