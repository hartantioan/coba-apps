<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:13px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>No.Dokumen</th>
            <th>Status</th>
            <th>Voider</th>
            <th>Tgl.Void</th>
            <th>Ket.Void</th>
            <th>Deleter</th>
            <th>Tgl.Delete</th>
            <th>Ket.Delete</th>
            <th>Pengguna</th>
            <th>Tgl. Post</th>
            <th>Kode Coa</th>
            <th>Nama Coa</th>
            <th>Partner Bisnis</th>
            <th>Plant</th>
            <th>Line</th>                        
            <th>Mesin</th>
            <th>Divisi</th>
            <th>Gudang</th>
            <th>Proyek</th>
            <th>Ket. 1</th>
            <th>Ket. 2</th>
            <th>Ket. 3</th>
            <th>Mata Uang</th>
            <th>Konversi</th>
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
                <td>{{ $row->code }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $row->voidUser()->exists() ? $row->voidUser->name : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' }}</td>
                <td>{{ $row->voidUser()->exists() ? $row->void_note : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->deleteUser->name : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '' }}</td>
                <td>{{ $row->deleteUser()->exists() ? $row->delete_note : '' }}</td>
                <td>{{ $row->user->name}}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $rowdetail->coa->code }}</td>
                <td>{{ $rowdetail->coa->name }}</td>
                <td align="center">{{ ($rowdetail->account()->exists() ? $rowdetail->account->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->place()->exists() ? $rowdetail->place->code : '-') }}</td>
                <td align="center">{{ ($rowdetail->line()->exists() ? $rowdetail->line->code : '-') }}</td>
                <td align="center">{{ ($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->department()->exists() ? $rowdetail->department->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->warehouse()->exists() ? $rowdetail->warehouse->name : '-') }}</td>
                <td align="center">{{ ($rowdetail->project()->exists() ? $rowdetail->project->name : '-') }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td>{{ $row->currency_id ? $row->currency->code : '-' }}</td>
                <td>{{ number_format($row->currency_rate,2,',','.') }}</td>
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