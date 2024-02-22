<table border="1" cellpadding="2" cellspacing="0" style="width:100%; font-size:13px;border-collapse: collapse;">
    <thead>
        <tr>
            <th align="center" rowspan="2">MENU</th>
            @foreach ($user as $row)
            <th align="center" colspan="5">{{ $row->employee_no.' - '.$row->name }}</th>
            @endforeach
        </tr>
        <tr>
            @foreach ($user as $row)
            <th>View</th>
            <th>Create</th>
            <th>Delete</th>
            <th>Void</th>
            <th>Journal</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($menu as $m)
            @if($m->sub()->exists())
                <tr>
                    <td>
                        {{ $m->name }}
                    </td>
                    @foreach ($user as $row)
                    <td colspan="5">
                        
                    </td>
                    @endforeach
                </tr>
                @foreach($m->sub()->where('status','1')->oldest('order')->get() as $msub)
                    @if($msub->sub()->exists())
                        <tr>
                            <td>
                                - - - {{ $msub->name }}
                            </td>
                            @foreach ($user as $row)
                            <td colspan="5">
                                        
                            </td>
                            @endforeach
                        </tr>
                        @foreach($msub->sub()->where('status','1')->oldest('order')->get() as $msub2)
                            @if($msub2->sub()->exists())

                            @else
                                <tr>
                                    <td>
                                        - - - - - - {{ $msub2->name }}
                                    </td>
                                    @foreach ($user as $row)
                                    <td align="center">
                                        {{ $row->checkMenu($msub2->id,'view') ? 'V' : '-' }}
                                    </td>
                                    <td align="center">
                                        {{ $row->checkMenu($msub2->id,'update') ? 'V' : '-' }}
                                    </td>
                                    <td align="center">
                                        {{ $row->checkMenu($msub2->id,'delete') ? 'V' : '-' }}
                                    </td>
                                    <td align="center">
                                        {{ $row->checkMenu($msub2->id,'void') ? 'V' : '-' }}
                                    </td>
                                    <td align="center">
                                        {{ $row->checkMenu($msub2->id,'journal') ? 'V' : '-' }}
                                    </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    @else
                        <tr>
                            <td>
                                - - - {{ $msub->name }}
                            </td>
                            @foreach ($user as $row)
                            <td align="center">
                                {{ $row->checkMenu($msub->id,'view') ? 'V' : '-' }}
                            </td>
                            <td align="center">
                                {{ $row->checkMenu($msub->id,'update') ? 'V' : '-' }}
                            </td>
                            <td align="center">
                                {{ $row->checkMenu($msub->id,'delete') ? 'V' : '-' }}
                            </td>
                            <td align="center">
                                {{ $row->checkMenu($msub->id,'void') ? 'V' : '-' }}
                            </td>
                            <td align="center">
                                {{ $row->checkMenu($msub->id,'journal') ? 'V' : '-' }}
                            </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            @else
                <tr>
                    <td>
                        {!! $m->name !!}
                    </td>
                    @foreach ($user as $row)
                    <td align="center">
                        {{ $row->checkMenu($m->id,'view') ? 'V' : '-' }}
                    </td>
                    <td align="center">
                        {{ $row->checkMenu($m->id,'update') ? 'V' : '-' }}
                    </td>
                    <td align="center">
                        {{ $row->checkMenu($m->id,'delete') ? 'V' : '-' }}
                    </td>
                    <td align="center">
                        {{ $row->checkMenu($m->id,'void') ? 'V' : '-' }}
                    </td>
                    <td align="center">
                        {{ $row->checkMenu($m->id,'journal') ? 'V' : '-' }}
                    </td>
                    @endforeach
                </tr>
            @endif
        @endforeach
    </tbody>
    <thead>
        <tr>
            <th align="center">PLANT</th>
            @foreach ($user as $row)
            <th align="center" colspan="5">{{ $row->employee_no.' - '.$row->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($place as $p)
        <tr>
            <th>{{ $p->code }}</th>
            @foreach ($user as $row)
            <th align="center" colspan="5">{{ $row->checkPlace($p->id) ? 'V' : '-' }}</th>
            @endforeach
        </tr>
        @endforeach
    </tbody>
    <thead>
        <tr>
            <th align="center">GUDANG</th>
            @foreach ($user as $row)
            <th align="center" colspan="5">{{ $row->employee_no.' - '.$row->name }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($warehouse as $w)
        <tr>
            <th>{{ $w->name }}</th>
            @foreach ($user as $row)
            <th align="center" colspan="5">{{ $row->checkWarehouse($w->id) ? 'V' : '-' }}</th>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>