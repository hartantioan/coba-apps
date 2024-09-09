<table class="bordered" id="table-result" style="min-width:2500px !important;zoom:0.6;">
    <thead class="sidebar-sticky" >
        <tr>
            <th class="center-align">Kode Coa</th>
            <th class="center-align">Nama Coa</th>
            <th class="center-align">{{ __('translations.date') }}</th>
            <th class="center-align">No.JE</th>
            <th class="center-align">Dok.Ref.</th>
            <th class="center-align">Debit FC</th>
            <th class="center-align">Kredit FC</th>
            <th class="center-align">Debit Rp</th>
            <th class="center-align">Kredit Rp</th>
            <th class="center-align">Total Rp</th>
            <th class="center-align">Keterangan 1</th>
            <th class="center-align">Keterangan 2</th>
            <th class="center-align">Keterangan 3</th>
            <th class="center-align">{{ __('translations.plant') }}</th>
            <th class="center-align">{{ __('translations.warehouse') }}</th>
            <th class="center-align">{{ __('translations.line') }}</th>
            <th class="center-align">{{ __('translations.engine') }}</th>
            <th class="center-align">Divisi </th>
            <th class="center-align">Proyek</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
        <tr style="font-weight:800;">
            <td width="200px">{{$row['code']}}</td>
            <td width="200px">{{$row['name']}}</td>
            <td colspan="7"></td>
            <td class="right-align">{{$row['balance']}}</td>
            <td colspan="9"></td>
        </tr>
            @php
                $balance = floatval(str_replace(',','.',str_replace('.','',$row['balance'])));
            @endphp
            @foreach ($row['details'] as $key => $rowdetail)
                @php
                    $balance = $rowdetail->type == '1' ? $balance + round($rowdetail->nominal,2) : $balance - round($rowdetail->nominal,2);
                @endphp
            <tr>
                <td>{{$rowdetail->coa->code}}</td>
                <td>{{$rowdetail->coa->name}}</td>
                <td>{{$rowdetail->journal->post_date}}</td>
                <td>{{$rowdetail->journal->code}}</td>
                <td>{{$rowdetail->journal->lookable_id ? $rowdetail->journal->lookable->code : '-'}}</td>
                <td>{{$rowdetail->type == '1' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0'}}</td>
                <td>{{$rowdetail->type == '2' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0'}}</td>
                <td>{{$rowdetail->type == '1' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0'}}</td>
                <td>{{$rowdetail->type == '2' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0'}}</td>
                <td>{{number_format($balance,2,',','.')}}</td>
                <td>{{$rowdetail->journal->note}}</td>
                <td>{{$rowdetail->note}}</td>
                <td>{{$rowdetail->note2}}</td>
                <td>{{$rowdetail->place()->exists() ? $rowdetail->place->code : '-'}}</td>
                <td>{{$rowdetail->warehouse()->Exists() ? $rowdetail->warehouse->name : '-'}}</td>
                <td>{{$rowdetail->line()->exists() ? $rowdetail->line->code : '-'}}</td>
                <td>{{$rowdetail->machine()->exists() ? $rowdetail->machine->code : '-'}}</td>
                <td>{{$rowdetail->department()->exists() ? $rowdetail->department->name : '-'}}</td>
                <td>{{$rowdetail->project()->exists() ? $rowdetail->project->code : '-'}}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>