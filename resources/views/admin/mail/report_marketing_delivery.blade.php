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
        <th style="font-size:12px;">Qty Sales / Day (M2)</th>
        <th style="font-size:12px;">Qty Sales / Month (M2)</th>
    </tr>
    @foreach ($data as $row)
    <tr>
        <td style="font-size:12px;" align="left">{{$row->tipe}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->d,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->m,0,",",".")}}</td>
    </tr>
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Brand</th>
        <th style="font-size:12px;">Qty Sales / Day (M2)</th>
        <th style="font-size:12px;">Qty Sales / Month (M2)</th>
    </tr>
    @foreach ($data3 as $row)
    <tr>
        <td style="font-size:12px;" align="left">{{$row->brand}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->d,0,",",".")}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->m,0,",",".")}}</td>
    </tr>
    @endforeach
</table>
<br>
<br>

<table>
    <tr>
        <th style="font-size:12px;">Item Code</th>
        <th style="font-size:12px;">Item Name</th>
        <th style="font-size:12px;">Stock (M2)</th>
      

    </tr>
    @php
    $totalm2=0;
    $totalpalet=0.00;
    @endphp
    @foreach ($data2 as $row)
    @php
    $totalm2=$totalm2+$row->total;

   
    @endphp
    <tr>
        <td style="font-size:12px;" align="left">{{$row->item_code}}</td>
        <td style="font-size:12px;" align="left">{{$row->item_name}}</td>
        <td style="font-size:12px;" align="right">{{number_format($row->total,0,",",".")}}</td>
       
    </tr>
    @endforeach
    <tr>
        <td style="font-size:12px;" align="right" colspan="2">TOTAL</td>
        <td style="font-size:12px;" align="right">{{number_format($totalm2,0,",",".")}}</td>
      
    </tr>
</table>