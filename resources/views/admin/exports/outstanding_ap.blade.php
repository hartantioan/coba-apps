<table>
    <thead>
        <tr>
            <th align="center">No.</th>
            <th align="center">No Invoice</th>
            <th align="center">Supplier/Vendor</th>
            <th align="center">Tgl.Post</th>
            <th align="center">Tgl.Terima</th>
            <th align="center">TOP(Hari)</th>
            <th align="center">Tgl.Jatuh Tempo</th>
            <th align="center">{{ __('translations.note') }}</th>
            <th align="center">Kurs</th>
            <th align="center">Sisa FC</th>
            <th align="center">Total</th>
            <th align="center">Dibayar</th>
            <th align="center">Sisa RP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['vendor'] }}</td>
                <td align="center">{{ $row['post_date'] }}</td>
                <td align="center">{{ $row['rec_date'] }}</td>
                <td align="center">{{ $row['top'] }}</td>
                <td align="center">{{ $row['due_date'] }}</td>
                <td align="center">{{ $row['note'] }}</td>
                <td align="center">{{ $row['kurs'] }}</td>
                <td align="center">{{ $row['real'] }}</td>
                <td align="right">{{ $row['grandtotal'] }}</td>
                <td align="center">{{ $row['payed'] }}</td>
                <td align="center">{{ $row['sisa'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="13" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>GrandTotal</td>
            <td>{{$totalall}}</td>
        </tr>
    </tfoot>
</table>