<table style="min-width:100%;max-width:100%;">
    <thead>
        <tr>
            <th class="center-align" colspan="10">Daftar Item GRPO</th>
        </tr>
        <tr>
            <th class="center-align">No</th>
            <th class="center-align">Dokumen</th>
            <th class="center-align">Tgl.Post</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">{{ __('translations.status') }}</th>
            <th class="center-align">Kode Item</th>
            <th class="center-align">Nama Item</th>
            <th class="center-align">{{ __('translations.unit') }}</th>
            <th class="center-align">Qty .</th>
            <th class="center-align">Qty Inv.</th>
            <th class="center-align">Tunggakan</th>
        </tr>
    </thead>
    <tbody>
        @if(count($data) > 0)
        @foreach($data as $key => $row)
            <tr>
                <td>{{ $key + 1 }}.</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td>{{ $row['status'] }}</td>
                <td>{{ $row['item_code'] }}</td>
                <td>{{ $row['item_name'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['qty_inv'] }}</td>
                <td>{{ $row['qty_balance'] }}</td>
                
            </tr>
        @endforeach
            
        @endif
    </tbody>
</table>