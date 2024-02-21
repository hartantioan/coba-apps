<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center" colspan="11" style="background-color: navy; color: white;border: 1px solid white;">Detail Item</th>
        </tr>
        <tr>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">No.</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tanggal.</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">No Faktur.</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama Supplier</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Nama Item</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Price</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Qty</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">SubTotal</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Discount(%)</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Total</th>
            <th align="center" style="background-color: navy; color: white;border: 1px solid white;">Tax</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
            @foreach($data as $key => $row)
                <tr>
                    <td style="border: 1px solid black;">{{ $key + 1 }}.</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->date }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->code }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_name }}</td>
                    <td style="border: 1px solid black;">{{ $row->item}}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->price,3,',','.')}}</td>
                    <td style="border: 1px solid black;">{{ $row->qty}}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->subtotal,3,',','.') }}</td>
                    <td style="border: 1px solid black;">{{ $row->discount }}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->total,3,',','.') }}</td>
                    <td style="border: 1px solid black;">{{ number_format($row->tax,3,',','.')}}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>