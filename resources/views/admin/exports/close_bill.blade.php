<table border="1" cellpadding="3" cellspacing="0" style="width:100%; font-size:10px;">
    <thead>
        <tr align="center">
            <th>No</th>
            <th>{{ __('translations.user') }}</th>
            <th>{{ __('translations.code') }}</th>
            <th>{{ __('translations.company') }}</th>
            <th>Tgl.Post</th>
            <th>{{ __('translations.note') }}</th>
            <th>Ref.CREQ</th>
            <th>Tgl.CREQ</th>
            <th>{{ __('translations.status') }}</th>
            <th>Coa</th>
            <th>Dist.Biaya</th>
            <th>{{ __('translations.plant') }}</th>
            <th>{{ __('translations.line') }}</th>
            <th>{{ __('translations.engine') }}</th>
            <th>{{ __('translations.division') }}</th>
            <th>Proyek</th>
            <th>Ket.1</th>
            <th>Ket.2</th>
            <th>Debit FC</th>
            <th>Kredit FC</th>
            <th>Debit Rp</th>
            <th>Kredit Rp</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $row)
            @foreach($row->closeBillCost as $key => $rowdetail)
            <tr align="center">
                <td>{{ $key+1 }}</td>
                <td>{{ $row->user->name }}</td>
                <td>{{ $row->code }}</td>
                <td>{{ $row->company->name }}</td>
                <td>{{ date('d/m/Y',strtotime($row->post_date)) }}</td>
                <td>{{ $row->note }}</td>
                <td>{{ $row->listCreq() }}</td>
                <td>{{ $row->listCreqDate() }}</td>
                <td>{!! $row->status() !!}</td>
                <td>{{ $rowdetail->coa->code.' - '.$rowdetail->coa->name }}</td>
                <td>{{ ($rowdetail->costDistribution()->exists() ? $rowdetail->costDistribution->code.' - '.$rowdetail->costDistribution->name : '-') }}</td>
                <td>{{ ($rowdetail->place()->exists() ? $rowdetail->place->code : '-') }}</td>
                <td>{{ ($rowdetail->line()->exists() ? $rowdetail->line->code : '-') }}</td>
                <td>{{ ($rowdetail->machine()->exists() ? $rowdetail->machine->name : '-') }}</td>
                <td>{{ ($rowdetail->division()->exists() ? $rowdetail->division->code : '-') }}</td>
                <td>{{ ($rowdetail->project()->exists() ? $rowdetail->project->name : '-') }}</td>
                <td>{{ $rowdetail->note }}</td>
                <td>{{ $rowdetail->note2 }}</td>
                <td>{{ number_format($rowdetail->nominal_debit_fc,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->nominal_credit_fc,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->nominal_debit,2,',','.') }}</td>
                <td>{{ number_format($rowdetail->nominal_credit,2,',','.') }}</td>
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>