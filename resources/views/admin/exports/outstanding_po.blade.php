<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Item PO Pembelian</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Buyer</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">Nama Vendor</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">Tipe Pengiriman</th>
            <th class="center-align">{{ __('translations.status') }}</th>
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.warehouse') }}</th>
            <th class="center-align">Grup Item</th>
            <th class="center-align">Kode Item</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">Keterangan 1</th>
            <th class="center-align">Keterangan 2</th>
            <th class="center-align">{{ __('translations.unit') }}</th>
            <th class="center-align">Qty PO.</th>
            <th class="center-align">Qty GR</th>
            <th class="center-align">Tunggakan</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['nama_supp'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['shipping_type'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['plant'] }}</td>
                <td>{{ $row['warehouse'] }}</td>
                <td>{{ $row['group_item'] }}</td>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['note1'] }}</td>
                <td>{{ $row['note2'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['qty_gr'] }}</td>
                <td>{{ $row['qty_balance'] }}</td>
                
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>