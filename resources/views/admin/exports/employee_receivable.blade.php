<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr align="center">
            <th class="center-align">{{ __('translations.no') }}.</th>
            <th class="center-align">No.FREQ</th>
            <th class="center-align">Karyawan</th>
            <th class="center-align">Tgl.Pengajuan</th>
            <th class="center-align">Tgl.Req.Bayar</th>
            <th class="center-align">{{ __('translations.note') }}</th>
            <th class="center-align">{{ __('translations.total') }}</th>
            <th class="center-align">{{ __('translations.tax') }}</th>
            <th class="center-align">{{ __('translations.wtax') }}</th>
            <th class="center-align">{{ __('translations.grandtotal') }}</th>
            <th class="center-align">Diterima</th>
            <th class="center-align">Dipakai</th>
            <th class="center-align">Sisa</th>
            <th class="center-align">Ref.Tutup BS Personal</th>
            <th class="center-align">Ref.Tutup BS</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr align="center" style="background-color:#eee;">
                <td class="center-align">{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['required_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td class="right-align">{{ $row['total'] }}</td>
                <td class="right-align">{{ $row['tax'] }}</td>
                <td class="right-align">{{ $row['wtax'] }}</td>
                <td class="right-align">{{ $row['grandtotal'] }}</td>
                <td class="right-align">{{ $row['received'] }}</td>
                <td class="right-align">{{ $row['used'] }}</td>
                <td class="right-align">{{ $row['balance'] }}</td>
                <td class="right-align">{{ $row['personal_cb'] }}</td>
                <td class="right-align">{{ $row['cb'] }}</td>
            </tr>
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="15" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
    <tfoot>
        <tr>
            <td colspan="12">Total</td>
            <td align="right">{{ $totalall }}</td>
            <td colspan="2">Total</td>
        </tr>
    </tfoot>
</table>