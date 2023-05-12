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
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m6 l4">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s5 m5">
                                        <h5 class="mb-0">1885</h5>
                                        <p class="no-margin">New</p>
                                        <p class="mb-0 pt-8">1,12,900</p>
                                    </div>
                                    <div class="col s7 m7 right-align">
                                        <i class="material-icons background-round mt-5 mb-5 gradient-45deg-purple-amber gradient-shadow white-text">perm_identity</i>
                                        <p class="mb-0">Total Clients</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col s12 m12 l12">
                            <div class="card padding-4 animate fadeLeft">
                                <div class="row">
                                    <div class="col s12 m12">
                                        <h5>TRIAL STOK REALTIME</h5>
                                        <table class="bordered" style="font-size:10px;">
                                            <thead>
                                                <tr>
                                                    <th class="center-align">No.</th>
                                                    <th class="center-align">Item</th>
                                                    <th class="center-align">Site</th>
                                                    <th class="center-align">Gudang</th>
                                                    <th class="center-align">Qty</th>
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
                                                        <td class="right-align">{{ number_format($row->valueNow(),3,',','.') }}</td>
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