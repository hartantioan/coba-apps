    <!-- BEGIN: Page Main-->
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <!-- Search for small screen-->
            <div class="container">
                <div class="row">
                    <div class="col s8 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item active"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
            <div class="container">
                <div class="section">
                    <div class="row">
                        <div class="col s12 m12 l12">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h5>HPP & STOK REALTIME</h5>
                                        <table class="bordered" style="font-size:10px;zoom:0.8;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No.</th>
                                                    <th class="center-align">Item (dari Stok)</th>
                                                    <th class="center-align">Shading</th>
                                                    <th class="center-align">Plant</th>
                                                    <th class="center-align">Gudang</th>
                                                    <th class="center-align">Area</th>
                                                    <th class="center-align">Shading</th>
                                                    <th class="center-align">Qty in Stock</th>
                                                    <th class="center-align">Qty Commited</th>
                                                    <th class="center-align">Rp</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($itemstocks as $key => $row)
                                                    <tr>
                                                        <td class="center-align">{{ ($key + 1) }}</td>
                                                        <td class="">{{ $row->item->code.' - '.$row->item->name }}</td>
                                                        <td class="">{{ $row->itemShading()->exists() ? $row->itemShading->code : '-' }}</td>
                                                        <td class="">{{ $row->place->code }}</td>
                                                        <td class="center-align">{{ $row->warehouse->name }}</td>
                                                        <td class="center-align">{{ $row->area()->exists() ? $row->area->name : '-' }}</td>
                                                        <td class="center-align">{{ $row->itemShading()->exists() ? $row->itemShading->code : '-' }}</td>
                                                        <td class="center-align">{{ number_format($row->qty,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                        <td class="center-align">{{ number_format($row->totalUndeliveredItem(),3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                        <td class="right-align">{{ number_format($row->valueNow(),3,',','.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        {{-- <h5>HPP REALTIME</h5>
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
                                                        <td class="">{{ $row->lookable->code }}</td>
                                                        <td class="">{{ $row->item->code.' - '.$row->item->name }}</td>
                                                        <td class="center-align">{{ $row->place->code }}</td>
                                                        <td class="center-align">{{ $row->warehouse->name }}</td>
                                                        <td class="center-align">{{ date('d/m/Y',strtotime($row->date)) }}</td>
                                                        <td class="right-align">{{ number_format($row->total_in,2,',','.') }}</td>
                                                        <td class="right-align">{{ number_format($row->total_out,2,',','.') }}</td>
                                                        <td class="right-align">{{ number_format($row->total_final,2,',','.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table> --}}
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