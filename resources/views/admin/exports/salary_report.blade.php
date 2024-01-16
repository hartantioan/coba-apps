<table style="border-collapse: collapse; width: 100%;">
    @foreach ($title as $key => $row_title)
        <tr>
            <td colspan="{{ count($data[$key]['thead']) }}" style="padding: 8px; text-align: left;">
                
            </td>
        </tr>
        <tr>
            <td colspan="{{ count($data[$key]['thead']) }}" style="padding: 8px; text-align: left;">
                <h4>Report Salary for {{ $row_title }}</h4>
            </td>
        </tr>
        <thead>
            <tr>
                @foreach ($data[$key]['thead'] as $row_thead)
                    <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: brown; color: white;">
                        {{ $row_thead }}
                    </th>
                @endforeach
            </tr>
        </thead>
        @if (isset($data[$key]['tbody']))
            <tbody>
                @foreach ($data[$key]['tbody'] as $row_tbody)
                    <tr>
                        @foreach ($row_tbody as $row_detail_tbody)
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">
                                {{ $row_detail_tbody }}
                            </td>
                        @endforeach 
                    </tr>  
                @endforeach
                
                <tr>
                    <td>TOTAL</td>
                    <td></td>
                    <td></td>
                    @foreach ($data[$key]['thead'] as $row_thead)
                        @foreach ($last_total as  $key_last=>$row_last)
                            @if($key_last == $row_thead)
                                <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">
                                    {{ $row_last }}
                                </td>
                            @endif
                        @endforeach
                    @endforeach
                </tr> 
            </tbody>
        @endif
    @endforeach
</table>
