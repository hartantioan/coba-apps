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

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">SO Daily (M2)</th>
        <th style="font-size:12px;">MOD Daily (M2)</th>
        <th style="font-size:12px;">SJ Daily (M2)</th>
        <th style="font-size:12px;">OS SO MTD (M2)</th>
        <th style="font-size:12px;">OS MOD MTD (M2)</th>
        <th style="font-size:12px;">SJ MTD (M2)</th>

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





    </tr>
    @php
    $i++;
    @endphp
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Tipe</th>
        <th style="font-size:12px;">SO Daily (M2)</th>
        <th style="font-size:12px;">MOD Daily (M2)</th>
        <th style="font-size:12px;">SJ Daily (M2)</th>
        <th style="font-size:12px;">OS SO MTD (M2)</th>
        <th style="font-size:12px;">OS MOD MTD (M2)</th>
        <th style="font-size:12px;">SJ MTD (M2)</th>

    </tr>
    @php
    $i=0;
    @endphp
    @foreach ($data2 as $row)

    <tr>
        <td style="font-size:12px;" align="left">{{$row->name}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>





    </tr>
    @php
    $i++;
    @endphp
    @endforeach
</table>
<br>
<br>

@php

$brand = [];
$ossoht = [];
$osmodht = [];
$sjht = [];
$ossogp = [];
$osmodgp = [];
$sjgp = [];
$totalossoht=0;
$totalossogp=0;
$totalosmodht=0;
$totalosmodgp=0;
$totalsjht=0;
$totalsjgp=0;


foreach ($data3 as $row) {

if (!in_array($row->brand, $brand)) {
$brand[] = $row->brand;
}
if ($row->tipe == 'HT') {
$ossoht[$row->brand] = $row->osso;
$osmodht[$row->brand] = $row->osmod;
$sjht[$row->brand] = $row->sj;
$totalossoht+=$row->osso;
$totalosmodht+=$row->osmod;
$totalsjht+=$row->sj;
} else {
$ossogp[$row->brand] = $row->osso;
$osmodgp[$row->brand] = $row->osmod;
$sjgp[$row->brand] = $row->sj;
$totalossogp+=$row->osso;
$totalosmodgp+=$row->osmod;
$totalsjgp+=$row->sj;
}
}
@endphp


<table>
    <tr>
        <th style="font-size:12px;">OEM</th>
        <th style="font-size:12px;" colspan="2">OS SO</th>
        <th style="font-size:12px;" colspan="2">OS MOD</th>
        <th style="font-size:12px;" colspan="2">SJ</th>
    </tr>
    <tr>
        <th style="font-size:12px;">Brand</th>
        <th style="font-size:12px;">HT</th>
        <th style="font-size:12px;">GP</th>
        <th style="font-size:12px;">HT</th>
        <th style="font-size:12px;">GP</th>
        <th style="font-size:12px;">HT</th>
        <th style="font-size:12px;">GP</th>

    </tr>

    <tr>
        <td style="font-size:12px;" align="left">TOTAL</td>
        <td style="font-size:12px;" align="right">{{number_format($totalossoht,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalossogp,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalosmodht,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalosmodgp,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalsjht,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalsjgp,0,",",".")}}</td>
    </tr>
    @foreach ($brand as $row)

    <tr>
        <td style="font-size:12px;" align="left">{{$row}}</td>
        <td style="font-size:12px;" align="right">{{number_format($ossoht[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($ossogp[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($osmodht[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($osmodgp[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($sjht[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($sjgp[$row]?? 0,0,",",".")}}</td>





    </tr>

    @endforeach
</table>
<br>
<br>



@php

$jenis = [];
$carlo = [];
$core = [];
$eod = [];
$mahesa = [];
$remo = [];
$valda = [];
$valerio = [];
$totalcarlo=0;
$totalcore=0;
$totaleod=0;
$totalmahesa=0;
$totalremo=0;
$totalvalda=0;
$totalvalerio=0;


foreach ($data4 as $row) {

if (!in_array($row->jenis, $jenis)) {
$jenis[] = $row->jenis;
}
if ($row->jenis == 'HT PLAIN') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}

}

if ($row->jenis == 'GLAZED SPECIAL MARBLE') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED LIGHT MARBLE') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED DARK MARBLE') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED SATIN LIGHT') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED SATIN DARK') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED MEDIUM MARBLE') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->jenis == 'GLAZED REGULAR MARBLE') {
if ($row->brand=='CARLO'){
$carlo[$row->jenis] = $row->endstock;
}
if ($row->brand=='CORE'){
$core[$row->jenis] = $row->endstock;
}
if ($row->brand=='EOD'){
$eod[$row->jenis] = $row->endstock;
}
if ($row->brand=='MAHESA'){
$mahesa[$row->jenis] = $row->endstock;
}
if ($row->brand=='REMO'){
$remo[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALDA'){
$valda[$row->jenis] = $row->endstock;
}
if ($row->brand=='VALERIO'){
$valerio[$row->jenis] = $row->endstock;
}
}

if ($row->brand=='CARLO'){
$totalcarlo+=$row->endstock;
}
if ($row->brand=='CORE'){
$totalcore+=$row->endstock;
}
if ($row->brand=='EOD'){
$totaleod+=$row->endstock;
}
if ($row->brand=='MAHESA'){
$totalmahesa+=$row->endstock;
}
if ($row->brand=='REMO'){
$totalremo+=$row->endstock;
}
if ($row->brand=='VALDA'){
$totalvalda+=$row->endstock;
}
if ($row->brand=='VALERIO'){

$totalvalerio+=$row->endstock;
}}
@endphp


<table>

    <tr>
        <th style="font-size:12px;">BRAND</th>
        <th style="font-size:12px;">CARLO</th>
        <th style="font-size:12px;">CORE</th>
        <th style="font-size:12px;">EOD</th>
        <th style="font-size:12px;">MAHESA</th>
        <th style="font-size:12px;">REMO</th>
        <th style="font-size:12px;">VALDA</th>
        <th style="font-size:12px;">VALERIO</th>

    </tr>
    <tr>
        <td style="font-size:12px;" align="left">TOTAL</td>
        <td style="font-size:12px;" align="right">{{number_format($totalcarlo?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalcore?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totaleod?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalmahesa?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalremo?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalvalda?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalvalerio?? 0,0,",",".")}}</td>





    </tr>


    @foreach ($jenis as $row)

    <tr>
        <td style="font-size:12px;" align="left">{{$row}}</td>
        <td style="font-size:12px;" align="right">{{number_format($carlo[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($core[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($eod[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($mahesa[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($remo[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($valda[$row]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($valerio[$row]?? 0,0,",",".")}}</td>





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
        <td style="font-size:12px;" align="left">&nbsp {{$row2}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalexptipe[$row][$row2]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalecotipe[$row][$row2]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalstdtipe[$row][$row2]?? 0,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($totalgtipe[$row][$row2]?? 0,0,",",".")}}</td>
    </tr>

    @foreach ($jenis as $row3)
    @if ($totalexpjenis[$row][$row2][$row3]==0 and $totalecojenis[$row][$row2][$row3]==0 and $totalstdjenis[$row][$row2][$row3]==0 and $totalgjenis[$row][$row2][$row3]==0)
    
    @else
    <tr>
        <td style="font-size:12px;" align="left">&nbsp&nbsp&nbsp {{$row3}}</td>
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
        <td style="font-size:12px;" align="left">&nbsp&nbsp&nbsp&nbsp&nbsp {{$row4}}</td>
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
        <td style="font-size:12px;" align="left">&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp {{$row5}}</td>
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