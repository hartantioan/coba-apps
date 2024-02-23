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
                        <div class="col s12 m6 l12">
                            
                            <div class="card padding-2 animate fadeLeft row">
                                 <div class="col s4 m6 l4">
                                    <h6 class="pl-2 pt-1">Absensi {{$start_date}} - {{$end_date}}</h6>
                                 </div>
                                 <div class="col s4 m6 l4">
                                 </div>
                                 <div class="col s4 m6 l4">
                                    <label class="" for="period_id">Period</label>
                                    <select class="browser-default" id="period_id" name="period_id" onchange="periodChange()"></select>
                                    
                                 </div>
                                 
                                 
                                 <div class="col s8 m6 l8">
                                       
                                       <div class="row">
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-deep-orange-orange gradient-shadow white-text animate fadeLeft">
                                                 <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                     <div style="flex: 1;">
                                                         <img src="{{ url('website/day.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                     </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                      <p class="no-margin center-align">Hari Efektif</p>
                                                      <h4 class="mb-0 white-text center-align" id="attendance_count">{{$attendance_count}}</h4>
                                                  </div>
                                                 </div>
                                             </div>
                                          </div>
                                         
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-purple-deep-orange gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/exit.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                         
                                                      </div>
                                                      <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Tepat Keluar &nbsp;</p>
                                                         <h4 class="mb-0 white-text center-align" id="tepatkeluar">{{$tepatkeluar}}</h4>
                                       
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-purple-amber gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                 
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/arrived.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                         
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Tepat Masuk</p>
                                                         <h4 class="mb-0 white-text center-align" id="tepatmasuk">{{$tepatmasuk}}</h4>
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-blue-indigo gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/absent.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                       
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Alpha</p>
                                                         <h4 class="mb-0 white-text center-align" id="total_absen">{{$total_absen}}</h4>
                                                         
                                                      </div>
                                                  
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-indigo-blue gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/no-data.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                         
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Tidak Absen Datang</p>
                                                         <h4 class="mb-0 white-text center-align" id="total_tidak_datang">{{$total_tidak_datang}}</h4>
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card gradient-45deg-indigo-purple gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/no-data (1).png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                       
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Tidak Absen Pulang</p>
                                                         <h4 class="mb-0 white-text center-align" id="total_tidak_pulang">{{$total_tidak_pulang}}</h4>
                                                      
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                 </div>
                                 <div class="col s4 m6 l4 ">
                                       <div class="app-todo">
                                          <div class="content-area" style="margin-top:0%; width:100%">
                                             <div class="app-wrapper">
                                             
                                             <div class="card card card-default scrollspy border-radius-6 fixed-width" >
                                                <div class="card-content p-0 pb-1" >
                                                   <div class="todo-header">
                                                   <div class="header-checkbox">
                                                      
                                                   </div>
                                                   <div class="list-content"></div>
                                                   <div class="todo-action">
                                                      
                                                      
                                                   </div>
                                                   </div>
                                                   <ul class="collection todo-collection" style="max-height: 18rem; overflow-y: auto; overflow-x: hidden;">
                                                      @foreach ($attendance_perday as $row_date )
                                                      <li class="collection-item todo-items">
                                                         <div class="list-content pl-2">
                                                         <div class="list-title-area">
                                                               <div class="list-title">{{$row_date['schedulefirst']}} - {{$row_date['schedulelast']}}</div>
                                                               
                                                         </div>
                                                         <div class="list-desc"> {{$row_date['time']}}</div>
                                                         </div>
                                                         <div class="list-right">
                                                         <div class="list-date"> {{$row_date['date']}} </div>
                                                         
                                                         </div>
                                                      </li>
                                                         
                                                      @endforeach
                                                      
                                                   
                                                   
                                                      <li class="collection-item no-data-found">
                                                         <h6 class="center-align font-weight-500">No Results Found</h6>
                                                      </li>
                                                      <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div></div>
                                                   </ul>
                                                </div>
                                             </div>
                                             </div>
                                          </div>
                                       </div>
                                       
                                 </div>
                                 <div class="col s12 m12 l12">
                                       
                                       <div class="row">
                                          <div class="col s12 m6 l4">
                                             <div class="card  deep-purple gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                  
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/leave.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                        
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Total Cuti</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_cuti">{{$counter_cuti}}</h4>
                                                         
                                                        
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card deep-purple darken-1 gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/bed.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                      
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Sakit</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_sakit">{{$counter_sakit}}</h4>
                                                         
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card deep-purple darken-2 gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                  
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/unemployment.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                     
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Ijin</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_ijin">{{$counter_ijin}}</h4>
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card indigo gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/svg.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                     
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Dinas Luar</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_dinas_luar">{{$counter_dinas_luar}}</h4>
                                                         
                                                       
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card indigo darken-1 gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/day.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                        
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Cuti Khsusus</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_cuti_kusus">{{$counter_cuti_kusus}}</h4>
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card  indigo darken-2 gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/verification.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                         
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">Dispen</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_dispen">{{$counter_dispen}}</h4>
                                                         
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col s12 m6 l4">
                                             <div class="card  blue darken-1 gradient-shadow min-height-100 white-text animate fadeLeft">
                                                <div class="padding-4" style="display: flex; align-items: center;min-height: 8rem;">
                                                   
                                                      <div style="flex: 1;">
                                                         <img src="{{ url('website/work-from-home.png') }}" class="responsive-img" alt="Day" style="height: 4rem; margin-right: 1rem;">
                                                   
                                                      </div>
                                                     <div style="flex: 3; display: flex; flex-direction: column; justify-content: center;">
                                                         <p class="no-margin center-align">WFH</p>
                                                         <h4 class="mb-0 white-text center-align" id="counter_wfh">{{$counter_wfh}}</h4>
                                                        
                                                      </div>
                                                   
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                 </div>
                            </div>

                        </div>
                        
                        
                        {{-- <div class="col s12 m12 l12">
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
                                                        <td class="center-align">{{ CustomHelper::formatConditionalQty($row->qty).' '.$row->item->uomUnit->code }}</td>
                                                        <td class="center-align">{{ number_format($row->totalUndeliveredItem(),2,',','.').' '.$row->item->uomUnit->code }}</td>
                                                        <td class="right-align">{{ number_format($row->valueNow(),2,',','.') }}</td>
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
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
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

<script>
   $(function(){
      select2ServerSide('#period_id', '{{ url("admin/select2/period") }}');
   });
   var originalHtml = $('.todo-collection').html();
   function periodChange(){
      
      $('.todo-collection').empty();
      if($('#period_id').val() != null){
         $.ajax({
            url: '{{ Request::url() }}/change_period',
            type: 'POST',
            dataType: 'JSON',
            data: {
               id: $('#period_id').val(),
            },
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                  loadingOpen('.modal-content');
            },
            success: function(response) {
               loadingClose('.modal-content');  
               $('#attendance_count').text(response.message['attendance_count']);
               $('#tepatkeluar').text(response.message['tepatkeluar']);
               $('#tepatmasuk').text(response.message['tepatmasuk']);
               $('#terlambat').text(response.message['terlambat']);
               $('#total_absen').text(response.message['total_absen']);
               $('#total_tidak_datang').text(response.message['total_tidak_datang']);
               $('#total_tidak_pulang').text(response.message['total_tidak_pulang']);
               $('#counter_cuti').text(response.message['counter_cuti']);
               $('#counter_sakit').text(response.message['counter_sakit']);
               $('#counter_ijin').text(response.message['counter_ijin']);
               $('#counter_dinas_luar').text(response.message['counter_dinas_luar']);
               $('#counter_cuti_kusus').text(response.message['counter_cuti_kusus']);

               $('#counter_dispen').text(response.message['counter_dispen']);
               $('#counter_wfh').text(response.message['counter_wfh']);
               $('.modal-content').scrollTop(0);

               
               var attendancePerDay = response.message['attendance_perday'];
               
               if (attendancePerDay.length > 0) {
               
                  $.each(attendancePerDay, function(index, rowDate) {
                     var listItem = '<li class="collection-item todo-items">' +
                           '<div class="list-content pl-2">' +
                           '<div class="list-title-area">' +
                           '<div class="list-title">' + rowDate.schedulefirst + ' - ' + rowDate.schedulelast + '</div>' +
                           '</div>' +
                           '<div class="list-desc">' + rowDate.time + '</div>' +
                           '</div>' +
                           '<div class="list-right">' +
                           '<div class="list-date">' + rowDate.date + '</div>' +
                           '</div>' +
                           '</li>';

                     $('.todo-collection').append(listItem);
                  });
               } 
            },
            error: function() {
                  $('.modal-content').scrollTop(0);
                  loadingClose('.modal-content');
                  swal({
                     title: 'Ups!',
                     text: 'Check your internet connection.',
                     icon: 'error'
                  });
            }
         });
        
      }else{
         $('.todo-collection').html(originalHtml);
         $('#attendance_count').text('{{ $attendance_count }}');
         $('#tepatkeluar').text('{{ $tepatkeluar }}');
         $('#tepatmasuk').text('{{ $tepatmasuk }}');
         $('#tepatkeluar').text('{{ $tepatkeluar }}');
         $('#terlambat').text('{{ $terlambat }}');
         $('#total_absen').text('{{ $total_absen }}');
         $('#total_tidak_datang').text('{{ $total_tidak_datang }}');
         $('#total_tidak_pulang').text('{{ $total_tidak_pulang }}');
         $('#counter_cuti').text('{{ $counter_cuti }}');
         $('#counter_sakit').text('{{ $counter_sakit }}');
         $('#counter_ijin').text('{{ $counter_ijin }}');
         $('#counter_dinas_luar').text('{{ $counter_dinas_luar }}');
         $('#counter_cuti_kusus').text('{{ $counter_cuti_kusus }}');
        
         $('#counter_dispen').text('{{ $counter_dispen }}');
         $('#counter_wfh').text('{{ $counter_wfh }}');
      }
   }
</script>