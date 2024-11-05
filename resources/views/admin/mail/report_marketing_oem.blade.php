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
