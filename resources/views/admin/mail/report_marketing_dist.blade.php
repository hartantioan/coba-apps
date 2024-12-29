<html>

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 70%;
        }

        td,
        th {
            border: 2px solid #dddddd;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>

<body>
    <p><strong>1A SALES REPORT - ITEM</strong></p>
    <table>
        <tr style="border:1px solid black;">
            <th style="font-size:12px;">Tipe</th>
            <th style="font-size:12px;">SO Daily (DUS)</th>
            <th style="font-size:12px;">MOD Daily (DUS)</th>
            <th style="font-size:12px;">SJ Daily (DUS)</th>
            <th style="font-size:12px;">OS SO MTD (DUS)</th>
            <th style="font-size:12px;">OS MOD MTD (DUS)</th>
            <th style="font-size:12px;">SJ MTD (DUS)</th>
            <th style="font-size:12px;">ASP MTD (RP/M2)</th>

        </tr>
        @php
        $i=0;
        @endphp
        @foreach ($data as $row)

        <tr>
            <td style="font-size:12px;" align="left">{{$row->name}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>





        </tr>
        @php
        $i++;
        @endphp
        @endforeach
    </table>
    <br>
    <br>

    <p><strong>1B - SALES REPORT (HOUSE BRAND) - REGION</strong></p>

    <table>
    <tr style="border:1px solid black;">
            <th style="font-size:12px;">PRO</th>
            <th style="font-size:12px;">SO Daily (DUS)</th>
            <th style="font-size:12px;">MOD Daily (DUS)</th>
            <th style="font-size:12px;">SJ Daily (DUS)</th>
            <th style="font-size:12px;">OS SO MTD (DUS)</th>
            <th style="font-size:12px;">OS MOD MTD (DUS)</th>
            <th style="font-size:12px;">SJ MTD (DUS)</th>
            <th style="font-size:12px;">ASP MTD (RP/M2)</th>
        </tr>
        @php
        $i=0;
        @endphp
        @foreach ($data2 as $row)

        <tr>
            <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
            
            <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>





        </tr>
        @php
        $i++;
        @endphp
        @endforeach
    </table>
    <br>
    <br>





    @php

    $dist=[];

    foreach($data3 as $row)
    {
    if (!in_array($row->cust, $dist)) {
    $dist[] = $row->cust;
    }
    }

    sort($dist);

    $ossoht = [];
    $ossogl = [];
    $osmodht = [];
    $osmodgl = [];
    $sjht = [];
    $sjgl = [];
    $aspht = [];
    $aspgl = [];

    foreach ($data3 as $row){
    if ($row->tipe=='HT')
    {
    $ossoht[$row->cust]=$row->sisaso;
    $osmodht[$row->cust]=$row->sisamod;
    $sjht[$row->cust]=$row->sj;
    $aspht[$row->cust]=$row->asp;

    }
    if ($row->tipe=='GLAZED')
    {
    $ossogl[$row->cust]=$row->sisaso;
    $osmodgl[$row->cust]=$row->sisamod;
    $sjgl[$row->cust]=$row->sj;
    $aspgl[$row->cust]=$row->asp;
    }
    }

    @endphp

    <p><strong>1C - SALES REPORT - KEY ACCOUNT</strong></p>

    <table>
        <tr>
            <th style="font-size:12px;">DISTRIBUTOR</th>
            <th style="font-size:12px;" colspan="2">OS SO (DUS)</th>
            <th style="font-size:12px;" colspan="2">OS MOD (DUS)</th>
            <th style="font-size:12px;" colspan="2">MTD SJ (DUS)</th>
            <th style="font-size:12px;" colspan="2">ASP MTD MONTHLY (RP/M2)</th>
        </tr>
        <tr>
            <th style="font-size:12px;"></th>
            <th style="font-size:12px;">HT</th>
            <th style="font-size:12px;">GP</th>
            <th style="font-size:12px;">HT</th>
            <th style="font-size:12px;">GP</th>
            <th style="font-size:12px;">HT</th>
            <th style="font-size:12px;">GP</th>
            <th style="font-size:12px;">HT</th>
            <th style="font-size:12px;">GP</th>

        </tr>

        @foreach ($dist as $row)
        @if ($ossoht[$row]==0 and $ossogl[$row]==0 and $osmodht[$row]==0 and $osmodgl[$row]==0 and $sjht[$row]==0 and $sjgl[$row]==0)

        @else

        <tr>
            <td style="font-size:12px;" align="left">{{$row}}</td>
            <td style="font-size:12px;" align="right">{{number_format($ossoht[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($ossogl[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($osmodht[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($osmodgl[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($sjht[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($sjgl[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($aspht[$row],0,",",".")}}</td>
            <td style="font-size:12px;" align="right">{{number_format($aspgl[$row],0,",",".")}}</td>





        </tr>
        @endif

        @endforeach
    </table>
    <br>
    <br>


    <strong> 2A - INVENTORY STOCK - GLOBAL
        <table>
            <tr>
                <th style="font-size:12px;">Tipe</th>
                <th style="font-size:12px;">PHP (DUS)</th>
                <th style="font-size:12px;">SALES (DUS)</th>
                <th style="font-size:12px;">END STOCK (DUS)</th>
                <th style="font-size:12px;">SALES VS STOCK (days)</th>

            </tr>

            @foreach ($data4 as $row)

            <tr>
                @if ($row->name=='TOTAL')
                <td style="font-size:12px;" align="left"><strong>{{$row->name}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->php,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->sales,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->stock,0,",",".")}}</td>
                <td style="font-size:12px;" align="right"><strong>{{number_format($row->life,0,",",".")}}</td>
                @else
                <td style="font-size:12px;" align="left">{{$row->name}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->php,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->sales,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->stock,0,",",".")}}</td>
                <td style="font-size:12px;" align="right">{{number_format($row->life,0,",",".")}}</td>
                @endif

            </tr>

            @endforeach
        </table>
        <br>
        <br>



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
            <td style="font-size:12px;" align="left">&nbsp;&nbsp;&nbsp; {{$row3}}</td>
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
            <td style="font-size:12px;" align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{$row4}}</td>
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
            <td style="font-size:12px;" align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{$row5}}</td>
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
</body>

</html>