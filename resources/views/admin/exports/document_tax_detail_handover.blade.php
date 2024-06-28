<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">Kode TT</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">Dari</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">Ke</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">Detail</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">Rp</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">{{ __('translations.name') }}</th>
            <th align="center"   style="background-color: navy; color: white;border: 1px solid white;">{{ __('translations.date') }}</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
            @foreach($data as $key => $row)
                <tr>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandover->code }}.</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandover->user->name }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandover->account->name ?? '' }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->transaction_code }}{{ $row->documentTax->code }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->total }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTax->npwp_name }}</td>
                    <td style="border: 1px solid black;">{{ $row->documentTaxHandover->post_date }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>