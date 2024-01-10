<table>
    @foreach ($title as $key => $row_title)
        <tr>
            <td colspan="{{ count($data[$key]['thead']) }}"><h4>Report Salary for {{ $row_title }}</h4></td>
        </tr>
        <thead>
            <tr>
                @foreach ($data[$key]['thead'] as $row_thead)
                    <th>{{ $row_thead }}</th>
                @endforeach
            </tr>
        </thead>
        @if (isset($data[$key]['tbody']))
            <tbody>
                <tr>
                    @foreach ($data[$key]['tbody'] as $row_tbody)
                        <td>{{ $row_tbody }}</td>
                    @endforeach
                </tr>
            </tbody>
        @endif
    @endforeach
</table>
