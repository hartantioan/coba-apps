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
                                            <h5>TRIAL HARGA RATA-RATA / COGS / HPP</h5>
                                            <table class="bordered" style="font-size:10px;">
                                                <thead>
                                                    <tr>
                                                        <th class="center-align">No.</th>
                                                        <th class="center-align">Item</th>
                                                        <th class="center-align">Site</th>
                                                        <th class="center-align">Gudang</th>
                                                        <th class="center-align">Qty In</th>
                                                        <th class="center-align">Price In</th>
                                                        <th class="center-align">Total In</th>
                                                        <th class="center-align">Qty Out</th>
                                                        <th class="center-align">Price Out</th>
                                                        <th class="center-align">Total Out</th>
                                                        <th class="center-align">Qty Final</th>
                                                        <th class="center-align">Price Final</th>
                                                        <th class="center-align">Total Final</th>
                                                        <th class="center-align">Referensi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($itemcogs as $key => $row)
                                                        <tr>
                                                            <td class="center-align">{{ ($key + 1) }}</td>
                                                            <td class="center-align">{{ $row->item->name }}</td>
                                                            <td class="center-align">{{ $row->place->name.' - '.$row->place->company->name }}</td>
                                                            <td class="center-align">{{ $row->warehouse->name }}</td>
                                                            <td class="center-align">{{ number_format($row->qty_in,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                            <td class="right-align">{{ number_format($row->price_in,2,',','.') }}</td>
                                                            <td class="right-align">{{ number_format($row->total_in,2,',','.') }}</td>
                                                            <td class="center-align">{{ number_format($row->qty_out,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                            <td class="right-align">{{ number_format($row->price_out,2,',','.') }}</td>
                                                            <td class="right-align">{{ number_format($row->total_out,2,',','.') }}</td>
                                                            <td class="center-align">{{ number_format($row->qty_final,3,',','.').' '.$row->item->uomUnit->code }}</td>
                                                            <td class="right-align">{{ number_format($row->price_final,2,',','.') }}</td>
                                                            <td class="right-align">{{ number_format($row->total_final,2,',','.') }}</td>
                                                            <th class="center-align">{{ $row->lookable->code }}</th>
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
                                @php
                                    /* $prq = [];
                                    foreach($pr as $key => $row){
                                        $prq[$key]['code'] = $row->code;
                                        foreach($row->purchaseRequestDetail as $keykey => $rowprd){
                                            foreach($rowprd->purchaseOrderDetail as $keydetail => $rowpod){
                                                $prq[$key]['po'][$keykey]['code'] = $rowpod->purchaseOrder->code.' - poid - '.$rowpod->id.' index '.$keykey;
                                                foreach($rowpod->goodReceiptDetail as $rowgrd){
                                                    $prq[$key]['po'][$keykey]['gr'][] = $rowgrd->goodReceipt->code;
                                                }
                                            }
                                        }
                                    }
                                    echo json_encode($prq); */
                                    $arr = [];
                                    foreach($pr1->purchaseRequestDetail as $keykey => $rowprd){
                                        foreach($rowprd->purchaseOrderDetail as $keydetail => $rowpod){
                                            $arr[$key]['po'][$keykey]['code'] = $rowpod->purchaseOrder->code.' - poid - '.$rowpod->id.' index '.$keykey;
                                            foreach($rowpod->goodReceiptDetail as $rowgrd){
                                                $arr[$key]['po'][$keykey]['gr'][] = $rowgrd->goodReceipt->code;
                                            }
                                        }
                                    }
                                    echo json_encode($arr);
                                @endphp
                                <div id="img-modal" class="modal white">
                                    <div class="modal-content">
                                        <div class="bg-img-div"></div>
                                        <p class="modal-header right modal-close">
                                            Skip Intro <span class="right"><i class="material-icons right-align">clear</i></span>
                                        </p>
                                        <div class="carousel carousel-slider center intro-carousel">
                                            <div class="carousel-fixed-item center middle-indicator">
                                                <div class="left">
                                                    <button class="movePrevCarousel middle-indicator-text btn btn-flat purple-text waves-effect waves-light btn-prev">
                                                        <i class="material-icons">navigate_before</i> <span class="hide-on-small-only">Prev</span>
                                                    </button>
                                                </div>

                                                <div class="right">
                                                    <button class=" moveNextCarousel middle-indicator-text btn btn-flat purple-text waves-effect waves-light btn-next">
                                                        <span class="hide-on-small-only">Next</span> <i class="material-icons">navigate_next</i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="carousel-item slide-1">
                                                <img src="{{ url('app-assets/images/gallery/intro-slide-1.png') }}" alt="" class="responsive-img animated fadeInUp slide-1-img">
                                                <h5 class="intro-step-title mt-7 center animated fadeInUp">Welcome to Materialize</h5>
                                                <p class="intro-step-text mt-5 animated fadeInUp">Materialize is a Material Design Admin
                                                    Template is the excellent responsive google material design inspired multipurpose admin
                                                    template. Materialize has a huge collection of material design animation & widgets, UI
                                                    Elements.</p>
                                            </div>
                                            <div class="carousel-item slide-2">
                                                <img src="{{ url('app-assets/images/gallery/intro-features.png') }}" alt="" class="responsive-img slide-2-img">
                                                <h5 class="intro-step-title mt-7 center">Example Request Information</h5>
                                                <p class="intro-step-text mt-5">Lorem ipsum dolor sit amet consectetur,
                                                    adipisicing elit.
                                                    Aperiam deserunt nulla
                                                    repudiandae odit quisquam incidunt, maxime explicabo.</p>
                                                <div class="row">
                                                    <div class="col s6">
                                                        <div class="input-field">
                                                            <label for="first_name">Name</label>
                                                            <input placeholder="Name" id="first_name" type="text" class="validate">
                                                        </div>
                                                    </div>
                                                    <div class="col s6">
                                                        <div class="input-field">
                                                            <select>
                                                                <option value="" disabled selected>Choose your option</option>
                                                                <option value="1">Option 1</option>
                                                                <option value="2">Option 2</option>
                                                                <option value="3">Option 3</option>
                                                            </select>
                                                            <label>Materialize Select</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="carousel-item slide-3">
                                                <img src="{{ url('app-assets/images/gallery/intro-app.png') }}" alt="" class="responsive-img slide-1-img">
                                                <h5 class="intro-step-title mt-7 center">Showcase App Features</h5>
                                                <div class="row">
                                                    <div class="col m5 offset-m1 s12">
                                                        <ul class="feature-list left-align">
                                                            <li><i class="material-icons">check</i> Email Application</li>
                                                            <li><i class="material-icons">check</i> Chat Application</li>
                                                            <li><i class="material-icons">check</i> Todo Application</li>
                                                        </ul>
                                                    </div>
                                                    <div class="col m6 s12">
                                                        <ul class="feature-list left-align">
                                                            <li><i class="material-icons">check</i>Contacts Application</li>
                                                            <li><i class="material-icons">check</i>Full Calendar</li>
                                                            <li><i class="material-icons">check</i> Ecommerce Application</li>
                                                        </ul>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col s12 center">
                                                            <button class="get-started btn waves-effect waves-light gradient-45deg-purple-deep-orange mt-3 modal-close">Get
                                                                Started</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- / Intro -->
                </div>
                <div class="content-overlay"></div>
            </div>
        </div>
    </div>
	{{-- <script src="{{ url('app-assets/js/scripts/intro.js') }}"></script> --}}
    <!-- END: Page Main-->