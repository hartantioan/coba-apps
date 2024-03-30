<table class="bordered" id="table-result" style="min-width:2500px !important;zoom:0.6;">
    <thead class="sidebar-sticky" >
        <tr>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Kode Coa</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="500px">Nama Coa</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="100px">Tanggal</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="200px">No.JE</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="200px">Dok.Ref.</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Debit Rp</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Kredit Rp</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Total Rp</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Debit FC</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Kredit FC</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Keterangan 1</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Keterangan 2</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Keterangan 3</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Plant</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Gudang</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Line</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Mesin</th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Divisi </th>
            <th class="center-align" style="background-color:white; border: 2px solid #000;" width="150px">Proyek</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $row)
        <tr style="font-weight:800;">
            <td width="200px">{{$row['code']}}</td>
            <td width="200px">{{$row['name']}}</td>
            <td colspan="5"></td>
            <td class="right-align">{{$row['balance']}}</td>
            <td colspan="11"></td>
        </tr>
        @if (isset($row['coa_code']) && is_array($row['coa_code']))
            @foreach ($row['coa_code'] as $key=>$row_sec)
            <tr>
                <td>{{$row_sec}}</td>
                <td>{{$row['coa_name'][$key]}}</td>
                <td>{{$row['j_postdate'][$key]}}</td>
                <td>{{$row['j_code'][$key]}}</td>
                <td>{{$row['j_lookable'][$key]}}</td>
                <td>{{$row['j_detail1'][$key]}}</td>
                <td>{{$row['j_detail2'][$key]}}</td>
                <td>{{$row['j_balance'][$key]}}</td>
                <td>{{$row['j_detail3'][$key]}}</td>
                <td>{{$row['j_detail4'][$key]}}</td>
                <td>{{$row['j_note'][$key]}}</td>
                <td>{{$row['j_note1'][$key]}}</td>
                <td>{{$row['j_note2'][$key]}}</td>
                <td>{{$row['j_place'][$key]}}</td>
                <td>{{$row['j_warehouse'][$key]}}</td>
                <td>{{$row['j_line'][$key]}}</td>
                <td>{{$row['j_machine'][$key]}}</td>
                <td>{{$row['j_department'][$key]}}</td>
                <td>{{$row['j_project'][$key]}}</td>
            </tr>
            @endforeach
        @endif
        
        @endforeach
    </tbody>
</table>