<table>
    <thead>
        <tr align="center">
            <th>{{ __('translations.no') }}.</th>
            <th>No.FREQ</th>
            <th>Karyawan</th>
            <th>Tgl.Pengajuan</th>
            <th>Tgl.Req.Bayar</th>
            <th>{{ __('translations.note') }}</th>
            <th>{{ __('translations.grandtotal') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
            <tr class="row_detail">
                <td>{{ $key + 1 }}</td>
                <td>{{ $row['code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['post_date'] }}</td>
                <td>{{ $row['required_date'] }}</td>
                <td>{{ $row['note'] }}</td>
                <td align="right">{{ $row['grandtotal'] }}</td>
            </tr>
            <tr>
                <td colspan="2" align="right" style="background-color:red;color:white;">PEMAKAIAN</td>
                <td style="background-color:red;color:white;">No.Dokumen</td>
                <td style="background-color:red;color:white;">Tgl.Post</td>
                <td style="background-color:red;color:white;">Status</td>
                <td style="background-color:red;color:white;">Keterangan</td>
                <td style="background-color:red;color:white;">Nominal</td>
            </tr>
            @foreach($row['details'] as $rowdetail)
            <tr>
                <td></td>
                <td></td>
                <td>{{ $rowdetail['no'] }}</td>
                <td>{{ $rowdetail['post_date'] }}</td>
                <td>{{ $rowdetail['status'] }}</td>
                <td>{{ $rowdetail['note'] }}</td>
                <td align="right">{{ $rowdetail['nominal'] }}</td>
            </tr>
            @endforeach
        @endforeach
        @if(count($data) == 0)
            <tr>
                <td colspan="7" align="center">
                    Data tidak ditemukan
                </td>
            </tr>
        @endif
        
    </tbody>
</table>