<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No.Dokumen</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.company') }}</th>
            <th>Tgl.Post</th>
            <th>Bulan</th>
            <th>{{ __('translations.note') }}</th>
            <th>Laba Berjalan</th>
            <th>{{ __('translations.status') }}</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Coa</th>
            <th>Debit</th>
            <th>Kredit</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->closingJournalDetail as $key1 => $rowdetail)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $no }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('F',strtotime($row->month)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ number_format($row->grandtotal,2,',','.') }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td align="right">{{ $rowdetail->type == '1' ? number_format($rowdetail->nominal,2,',','.') : '0,00' }}</td>
                <td align="right">{{ $rowdetail->type == '2' ? number_format($rowdetail->nominal,2,',','.') : '0,00' }}</td>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>