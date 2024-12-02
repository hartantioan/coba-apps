
<html>

<head>
<style>
    table, p {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 50%;
    }

    td,
    th,tr {
        border: 1px solid black;
        padding: 8px;
    }

</style>
</head>

<body>
 
    <table>
        <tr>
            <th style="font-size:12px;">Tanggal</th>
            <th style="font-size:12px;">Value</th>
          

        </tr>
       
        @foreach ($data as $row)

        <tr>
            <td style="font-size:12px;" align="left">{{$row->tanggal}}</td>
            <td style="font-size:12px;" align="right">{{number_format($row->total,0,",",".")}}</td>
        </tr>
       
        @endforeach
    </table>
    <br>
    <br>
    <p>Note : Value incoming payment di luar Bank Ayat Silang dan Undefined Customer
  

</body>

</html>