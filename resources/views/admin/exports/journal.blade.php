<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>Pengguna</th>
            <th>Perusahaan</th>
            <th>Kode</th>
            <th>Referensi</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
            <th>Tgl. Post</th>
            <th>Tgl. Jatuh Tempo</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Coa</th>
            <th>Plant</th>
            <th>Partner Bisnis</th>
            <th>Item</th>
            <th>Departemen</th>
            <th>Gudang</th>
            <th>Debit Real</th>
            <th>Kredit Real</th>
            <th>Debit Convert</th>
            <th>Kredit Convert</th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach($data as $key => $row)
            @foreach($row->journalDetail()->where(function($query){
                $query->whereHas('coa',function($query){
                    $query->orderBy('code');
                })
                ->orderBy('type');
            })->get() as $rowdetail)
            <tr align="center" style="background-color:#d6d5d5;">
                <td>{{ $no }}</td>
                <td>{{ $row->user->name}}</td>
                <td>{{ $row->company->name ?? '' }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->lookable_type ? $row->lookable->code : '-' }}</td>
                <td>{{ $row->currency_id ? $row->currency->code : '-' }}</td>
                <td>{{ number_format($row->currency_rate,3,',','.') }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ date('d/m/Y',strtotime($row->due_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $rowdetail->coa->name }}</td>
                <td align="center">{{ ($rowdetail->place()->exists() ? $rowdetail->place->code.' - '.$rowdetail->place->company->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->account()->exists() ? $rowdetail->account->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->item()->exists() ? $rowdetail->item->code.' - '.$rowdetail->item->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->department()->exists() ? $rowdetail->department->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->warehouse()->exists() ? $rowdetail->warehouse->name : '-') }}</td>
                <td align="right">{{ ($rowdetail->type == '1' ? number_format($rowdetail->nominal_fc,3,',','.') : '') }}</td>
                <td align="right">{{ ($rowdetail->type == '2' ? number_format($rowdetail->nominal_fc,3,',','.') : '') }}</td>
                <td align="right">{{ ($rowdetail->type == '1' ? number_format($rowdetail->nominal,3,',','.') : '') }}</td>
                <td align="right">{{ ($rowdetail->type == '2' ? number_format($rowdetail->nominal,3,',','.') : '') }}</td>
            </tr>
            @php
                $no++;
            @endphp
            @endforeach
        @endforeach
    </tbody>
</table>