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
        <th style="font-size:12px;">ASP MTD (M2)</th>
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
        @if ($i==0)
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
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
        <th style="font-size:12px;">ASP MTD (M2)</th>
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
        @if ($i==0)
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
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
        <th style="font-size:12px;">ASP MTD (M2)</th>
    </tr>
    @php
    $i=0;
    @endphp
    @foreach ($data3 as $row)

    <tr>

        @if ($row->tipe=='HT' || $row->tipe=='GLAZED')
        <td style="font-size:12px;" align="left"><strong>{{$row->tipe}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right"><strong>{{number_format($row->sjm,0,",",".")}}</td>
        @else
        <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtyso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtymod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->qtysj,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisaso,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sisamod,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->sjm,0,",",".")}}</td>
        @endif
        @if ($row->tipe=='HT' || $row->tipe=='GLAZED')
        <td style="font-size:12px;" align="right">-</td>

        @else

        <td style="font-size:12px;" align="right">{{number_format($row->asp,0,",",".")}}</td>

        @endif
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
        <th style="font-size:12px;">PHP (M2)</th>
        <th style="font-size:12px;">SALES (M2)</th>
        <th style="font-size:12px;">END STOCK (M2)</th>
        <th style="font-size:12px;">SALES VS STOCK (M2)</th>
        
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