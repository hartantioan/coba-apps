<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">No.</th>
            <th class="center-align">No Invoice</th>
            <th class="center-align">Supplier/Vendor</th>
            <th class="center-align">No PO</th>
            <th class="center-align">TGL Post</th>
            <th class="center-align">TGL Terima</th>
            <th class="center-align">TOP(Hari)</th>
            <th class="center-align">TGL Tenggat</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">Note 1</th>
            <th class="center-align">Note 2</th>
            <th class="center-align">Qty</th>
            <th class="center-align">Satuan</th>
            <th class="center-align">Harga Satuan</th>
            <th class="center-align">Total</th>
            <th class="center-align">PPN</th>
            <th class="center-align">PPH</th>
            <th class="center-align">Grandtotal</th>
            <th class="center-align">Dibayar</th>
            <th class="center-align">Sisa</th>   
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td rowspan="{{ count($row['details']) }}">{{ $key+1 }}</td>
                <td rowspan="{{ count($row['details']) }}">{{ $row['code'] }}</td>
                <td rowspan="{{ count($row['details']) }}">{{ $row['vendor'] }}</td>
                <td>{{ $row['details'][0]['po'] }}</td>
                <td class="center-align" rowspan="{{ count($row['details']) }}">{{ $row['post_date'] }}</td>
                <td class="center-align" rowspan="{{ count($row['details']) }}">{{ $row['rec_date'] }}</td>
                <td class="center-align">{{ $row['details'][0]['top'] }}</td>
                <td class="center-align" rowspan="{{ count($row['details']) }}">{{ $row['due_date'] }}</td>
                <td>{{ $row['details'][0]['item_name'] }}</td>
                <td>{{ $row['details'][0]['note1'] }}</td>
                <td>{{ $row['details'][0]['note2'] }}</td>
                <td class="center-align">{{ $row['details'][0]['qty'] }}</td>
                <td class="center-align">{{ $row['details'][0]['unit'] }}</td>
                <td class="right-align">{{ $row['details'][0]['price_o'] }}</td>
                <td class="right-align">{{ $row['details'][0]['total'] }}</td>
                <td class="right-align">{{ $row['details'][0]['ppn'] }}</td>
                <td class="right-align">{{ $row['details'][0]['pph'] }}</td>
                <td class="right-align" rowspan="{{ count($row['details']) }}">{{ $row['grandtotal'] }}</td>
                <td class="center-align" rowspan="{{ count($row['details']) }}">{{ $row['payed'] }}</td>
                <td class="center-align" rowspan="{{ count($row['details']) }}">{{ $row['sisa'] }}</td>
            </tr>
            @foreach($row['details'] as $index => $detail)
                @if($index > 0)
                    <tr>
                        <td>{{ $detail['po'] }}</td>
                        <td class="center-align">{{ $detail['top'] }}</td>
                        <td>{{ $detail['item_name'] }}</td>
                        <td>{{ $detail['note1'] }}</td>
                        <td>{{ $detail['note2'] }}</td>
                        <td class="center-align">{{ $detail['qty'] }}</td>
                        <td class="center-align">{{ $detail['unit'] }}</td>
                        <td class="right-align">{{ $detail['price_o'] }}</td>
                        <td class="right-align">{{ $detail['total'] }}</td>
                        <td class="right-align">{{ $detail['ppn'] }}</td>
                        <td class="right-align">{{ $detail['pph'] }}</td>
                        <td class="center-align">{{ $index }}</td>
                        <td class="center-align">{{ $index }}</td>
                    </tr>
                @endif
            @endforeach  
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="19" align="center">
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