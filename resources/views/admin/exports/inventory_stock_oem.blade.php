@php

    $brand = [];
    $tipe = [];
    $jenis = [];
    $pattern = [];
    $code = [];
    foreach ($data5 as $row)
    {
    if (!in_array($row->brand, $brand)) {
    $brand[] = $row->brand;
    }
    if (!in_array($row->tipe, $tipe)) {
    $tipe[] = $row->tipe;
    }
    if (!in_array($row->jenis, $jenis)) {
    $jenis[] = $row->jenis;
    }
    if (!in_array($row->pattern, $pattern)) {
    $pattern[] = $row->pattern;
    }
    if (!in_array($row->code, $code)) {
    $code[] = $row->code;
    }
    }





    $totalexp = [];
    $totaleco = [];
    $totalstd = [];
    $totalg = [];

    foreach ($brand as $row)
    {
    $totalexp[$row]=0;
    $totaleco[$row]=0;
    $totalstd[$row]=0;
    $totalg[$row]=0;
    }

    foreach ($data5 as $row)
    {
    if($row->grade=='EXP'){
    $totalexp[$row->brand]+=$row->endstock;
    }
    if($row->grade=='ECO'){
    $totaleco[$row->brand]+=$row->endstock;
    }
    if($row->grade=='STD'){
    $totalstd[$row->brand]+=$row->endstock;
    }
    if($row->grade=='G'){
    $totalg[$row->brand]+=$row->endstock;
    }
    }

    //tipe

    $totalexptipe = [];
    $totalecotipe = [];
    $totalstdtipe = [];
    $totalgtipe = [];

    foreach ($brand as $row)
    {
    foreach ($tipe as $row2){
    $totalexptipe[$row][$row2]=0;
    $totalecotipe[$row][$row2]=0;
    $totalstdtipe[$row][$row2]=0;
    $totalgtipe[$row][$row2]=0;}
    }

    foreach($data5 as $row){
    if($row->grade=='EXP'){
    $totalexptipe[$row->brand][$row->tipe]+=$row->endstock;
    }
    if($row->grade=='ECO'){
    $totalecotipe[$row->brand][$row->tipe]+=$row->endstock;
    }
    if($row->grade=='STD'){
    $totalstdtipe[$row->brand][$row->tipe]+=$row->endstock;
    }
    if($row->grade=='G'){
    $totalgtipe[$row->brand][$row->tipe]+=$row->endstock;
    }
    }

    //jenis

    $totalexpjenis = [];
    $totalecojenis = [];
    $totalstdjenis = [];
    $totalgjenis = [];

    foreach ($brand as $row)
    {
    foreach ($tipe as $row2){
    foreach ($jenis as $row3){
    $totalexpjenis[$row][$row2][$row3]=0;
    $totalecojenis[$row][$row2][$row3]=0;
    $totalstdjenis[$row][$row2][$row3]=0;
    $totalgjenis[$row][$row2][$row3]=0;}}
    }

    foreach($data5 as $row){
    if($row->grade=='EXP'){
    $totalexpjenis[$row->brand][$row->tipe][$row->jenis]+=$row->endstock;
    }
    if($row->grade=='ECO'){
    $totalecojenis[$row->brand][$row->tipe][$row->jenis]+=$row->endstock;
    }
    if($row->grade=='STD'){
    $totalstdjenis[$row->brand][$row->tipe][$row->jenis]+=$row->endstock;
    }
    if($row->grade=='G'){
    $totalgjenis[$row->brand][$row->tipe][$row->jenis]+=$row->endstock;
    }
    }


    //pattern

    $totalexppattern = [];
    $totalecopattern = [];
    $totalstdpattern = [];
    $totalgpattern = [];

    foreach ($brand as $row)
    {
    foreach ($tipe as $row2){
    foreach ($jenis as $row3){
    foreach ($pattern as $row4){
    $totalexppattern[$row][$row2][$row3][$row4]=0;
    $totalecopattern[$row][$row2][$row3][$row4]=0;
    $totalstdpattern[$row][$row2][$row3][$row4]=0;
    $totalgpattern[$row][$row2][$row3][$row4]=0;}}}
    }

    foreach($data5 as $row){
    if($row->grade=='EXP'){
    $totalexppattern[$row->brand][$row->tipe][$row->jenis][$row->pattern]+=$row->endstock;
    }
    if($row->grade=='ECO'){
    $totalecopattern[$row->brand][$row->tipe][$row->jenis][$row->pattern]+=$row->endstock;
    }
    if($row->grade=='STD'){
    $totalstdpattern[$row->brand][$row->tipe][$row->jenis][$row->pattern]+=$row->endstock;
    }
    if($row->grade=='G'){
    $totalgpattern[$row->brand][$row->tipe][$row->jenis][$row->pattern]+=$row->endstock;
    }
    }

    //code

    $totalexpcode = [];
    $totalecocode = [];
    $totalstdcode = [];
    $totalgcode = [];

    foreach ($brand as $row)
    {
    foreach ($tipe as $row2){
    foreach ($jenis as $row3){
    foreach ($pattern as $row4){
    foreach ($code as $row5){
    $totalexpcode[$row][$row2][$row3][$row4][$row5]=0;
    $totalecocode[$row][$row2][$row3][$row4][$row5]=0;
    $totalstdcode[$row][$row2][$row3][$row4][$row5]=0;
    $totalgcode[$row][$row2][$row3][$row4][$row5]=0;}}}}
    }

    foreach($data5 as $row){
    if($row->grade=='EXP'){
    $totalexpcode[$row->brand][$row->tipe][$row->jenis][$row->pattern][$row->code]+=$row->endstock;
    }
    if($row->grade=='ECO'){
    $totalecocode[$row->brand][$row->tipe][$row->jenis][$row->pattern][$row->code]+=$row->endstock;
    }
    if($row->grade=='STD'){
    $totalstdcode[$row->brand][$row->tipe][$row->jenis][$row->pattern][$row->code]+=$row->endstock;
    }
    if($row->grade=='G'){
    $totalgcode[$row->brand][$row->tipe][$row->jenis][$row->pattern][$row->code]+=$row->endstock;
    }
    }


    @endphp

    <p><strong>INVENTORY STOCK - SHADING</strong></p>

    <table>


        <tr>
            <th style="font-size:12px;">BRAND</th>
            <th style="font-size:12px;">EXP</th>
            <th style="font-size:12px;">ECO</th>
            <th style="font-size:12px;">STD</th>
            <th style="font-size:12px;">G</th>

        </tr>

        @foreach ($brand as $row)
        <tr>
            <td style="font-size:12px;" align="left"><strong>{{$row}}</strong></td>
            <td style="font-size:12px;" align="right"><strong>{{number_format($totalexp[$row]?? 0,0,",",".")}}</strong></td>
            <td style="font-size:12px;" align="right"><strong>{{number_format($totaleco[$row]?? 0,0,",",".")}}</strong></td>
            <td style="font-size:12px;" align="right"><strong>{{number_format($totalstd[$row]?? 0,0,",",".")}}</strong></td>
            <td style="font-size:12px;" align="right"><strong>{{number_format($totalg[$row]?? 0,0,",",".")}}</strong></td>
        </tr>

        @foreach ($tipe as $row2)
        <tr>
            <td style="font-size:12px;" align="left">&nbsp; {{$row2}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalexptipe[$row][$row2]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalecotipe[$row][$row2]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalstdtipe[$row][$row2]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalgtipe[$row][$row2]?? 0,0,",",".")}}</td>
        </tr>

        @foreach ($jenis as $row3)
        @if ($totalexpjenis[$row][$row2][$row3]==0 and $totalecojenis[$row][$row2][$row3]==0 and $totalstdjenis[$row][$row2][$row3]==0 and $totalgjenis[$row][$row2][$row3]==0)

        @else
        <tr>
            <td style="font-size:12px;" align="left">_{{$row3}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalexpjenis[$row][$row2][$row3]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalecojenis[$row][$row2][$row3]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalstdjenis[$row][$row2][$row3]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalgjenis[$row][$row2][$row3]?? 0,0,",",".")}}</td>
        </tr>
        @endif

        @foreach ($pattern as $row4)
        @if ($totalexppattern[$row][$row2][$row3][$row4]==0 and $totalecopattern[$row][$row2][$row3][$row4]==0 and $totalstdpattern[$row][$row2][$row3][$row4]==0 and $totalgpattern[$row][$row2][$row3][$row4]==0)

        @else
        <tr>
            <td style="font-size:12px;" align="left">___{{$row4}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalexppattern[$row][$row2][$row3][$row4]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalecopattern[$row][$row2][$row3][$row4]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalstdpattern[$row][$row2][$row3][$row4]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalgpattern[$row][$row2][$row3][$row4]?? 0,0,",",".")}}</td>
        </tr>
        @endif

        @foreach ($code as $row5)
        @if ($totalexpcode[$row][$row2][$row3][$row4][$row5]==0 and $totalecocode[$row][$row2][$row3][$row4][$row5]==0 and $totalstdcode[$row][$row2][$row3][$row4][$row5]==0 and $totalgcode[$row][$row2][$row3][$row4][$row5]==0)

        @else
        <tr>
            <td style="font-size:12px;" align="left">______{{$row5}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalexpcode[$row][$row2][$row3][$row4][$row5]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalecocode[$row][$row2][$row3][$row4][$row5]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalstdcode[$row][$row2][$row3][$row4][$row5]?? 0,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($totalgcode[$row][$row2][$row3][$row4][$row5]?? 0,0,",",".")}}</td>
        </tr> 
        @endif
        @endforeach


       

        @endforeach

        @endforeach

        @endforeach

        @endforeach

    </table>