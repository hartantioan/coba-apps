    <!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="content-wrapper-before blue-grey lighten-5"></div>
        <div class="col s12">
            <div class="container">
                <div class="section">
                    
                    <div class="row">
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">{{$total_telat_masuk}}</h5>
                                        <p style="font-size: x-small" class>Tidak Check Log Masuk</p>
                                    </div>
                                    <div class="col s7 m7 right-align" style="font-size: 0.67rem;color: dimgrey;font-weight: bold;">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">fullscreen</i>
                                        <p class="mb-0">periode {{$start_date}} - {{$end_date}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">{{$total_telat_keluar}}</h5>
                                        <p style="font-size: x-small" class>Tidak Check Log Keluar</p>
                                    </div>
                                    <div class="col s7 m7 right-align" style="font-size: 0.67rem;color: dimgrey;font-weight: bold;">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">fullscreen_exit</i>
                                        <p class="mb-0">periode {{$start_date}} - {{$end_date}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">{{$total_absen}}</h5>
                                        <p style="font-size: x-small" class>Absen</p>
                                    </div>
                                    <div class="col s7 m7 right-align" style="font-size: 0.67rem;color: dimgrey;font-weight: bold;">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">fullscreen_exit</i>
                                        <p class="mb-0">periode {{$start_date}} - {{$end_date}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">{{$attendance_count}}</h5>
                                        <p style="font-size: x-small" class>Total Masuk</p>
                                    </div>
                                    <div class="col s7 m7 right-align" style="font-size: 0.67rem;color: dimgrey;font-weight: bold;">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">fullscreen_exit</i>
                                        <p class="mb-0">periode {{$start_date}} - {{$end_date}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l8">
                            <div class="card padding-3 animate fadeLeft">
                                <ul class="collapsible collapsible-accordion">
                                    <li>
                                        <div class="collapsible-header"><i class="material-icons">filter_list</i> Detail Masuk</div>
                                        <div class="collapsible-body">
                                            <div class="row" style="max-height: 8rem;overflow-y: scroll;">
                                                @foreach ($attendance as $row_attendance )
                                                    <div class="card card-border z-depth-2">
                            
                                                        <div class="card-content">
                                                            <h6 class="font-weight-900 text-uppercase"><a href="#">{{$row_attendance['date']}}</a></h6>
                                                            @if ($row_attendance["in"] == 1 && $row_attendance["out"] == 1)
                                                                <p>Tepat Waktu</p>
                                                            @elseif ($row_attendance["in"] == 0 && $row_attendance["out"] == 1)
                                                                <p>Tidak Check Log Masuk</p>
                                                            @elseif ($row_attendance["in"] == 1 && $row_attendance["out"] == 0)
                                                                <p>Tidak Check Log Keluar</p>
                                                            @else
                                                                <p>Tidak Masuk</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            
                        </div>
                        <div class="col s12 m12 l12">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h5>HPP & STOK REALTIME</h5>
                                        <table class="bordered" style="font-size:10px;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No.</th>
                                                    <th class="center-align">Item</th>
                                                    <th class="center-align">Plant</th>
                                                    <th class="center-align">Gudang</th>
                                                    <th class="center-align">Qty in Stock</th>
                                                    <th class="center-align">Qty Commited</th>
                                                    <th class="center-align">Rp</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemstocks as $key => $row)
                                                    <tr>
                                                        <td class="center-align">{{ ($key + 1) }}</td>
                                                        <td class="center-align">{{ $row->item->name }}</td>
                                                        <td class="center-align">{{ $row->place->name.' - '.$row->place->company->name }}</td>
                                                        <td class="center-align">{{ $row->warehouse->name }}</td>
                                                        <td class="center-align">{{ number_format($row->qty,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                        <td class="center-align">{{ number_format($row->totalUndeliveredItem(),3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                        <td class="right-align">{{ number_format($row->valueNow(),3,',','.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <h5>HPP REALTIME</h5>
                                        <table class="bordered" style="font-size:10px;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No.</th>
                                                    <th class="center-align">Referensi</th>
                                                    <th class="center-align">Item</th>
                                                    <th class="center-align">Plant</th>
                                                    <th class="center-align">Gudang</th>
                                                    <th class="center-align">Date</th>
                                                    <th class="center-align">Nominal Masuk</th>
                                                    <th class="center-align">Nominal Keluar</th>
                                                    <th class="center-align">Nominal Akhir</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemcogs as $key => $row)
                                                    <tr>
                                                        <td class="center-align">{{ ($key + 1) }}</td>
                                                        <td class="center-align">{{ $row->lookable->code }}</td>
                                                        <td class="center-align">{{ $row->item->name }}</td>
                                                        <td class="center-align">{{ $row->place->name }}</td>
                                                        <td class="center-align">{{ $row->warehouse->name }}</td>
                                                        <td class="center-align">{{ date('d/m/y',strtotime($row->date)) }}</td>
                                                        <td class="right-align">{{ number_format($row->total_in,2,',','.') }}</td>
                                                        <td class="right-align">{{ number_format($row->total_out,2,',','.') }}</td>
                                                        <td class="right-align">{{ number_format($row->total_final,2,',','.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="intro">
                    <div class="row">
                        <div class="col s12">
                            
                        </div>
                    </div>
                </div>
                <!-- / Intro -->
            </div>
            <div class="content-overlay"></div>
        </div>
    </div>
</div>