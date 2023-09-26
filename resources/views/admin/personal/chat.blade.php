@php
    use Carbon\Carbon;
@endphp
<style>
  .circle{
    width:50px !important;
  }
  
  .chat-application .app-chat .chat-content .chat-content-area .chat-area .chats .chat {
    margin: 0.3rem 0.5rem;
  }
</style>
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/app-chat.css') }}">
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
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(2))) }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ Str::title(str_replace('_',' ',Request::segment(3))) }}
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="websocket" data-from-user-id="{{ Auth::user()->id }}" hidden></div>
    <div id="tokenid" data-from-user-token="{{ Auth::user()->token }}" hidden></div>


    <div id="card-widgets" class="seaction">
      <div class="row">
        <div class="col s12 m12 xl12">
          <div class="chat-application">
            <div class="app-chat">
              <div class="card card card-default scrollspy border-radius-6 ">
                <div class="card-content chat-content p-0">
                  <!-- Sidebar Area -->
                  <div class="sidebar-left sidebar-fixed animate fadeUp animation-fast">
                    <div class="sidebar animate fadeUp">
                      <div class="sidebar-content">
                        <div id="sidebar-list" class="sidebar-menu chat-sidebar list-group position-relative">
                          <div class="sidebar-list-padding app-sidebar sidenav" id="chat-sidenav">
                            <!-- Sidebar Header -->
                            <div class="sidebar-header">
                              <div class="row valign-wrapper">
                                <div class="col s2 media-image pr-0">
                                  <img src="{{$data_user->photo()}}" alt="" class="circle z-depth-2 responsive-img">
                                </div>
                                <div class="col s10">
                                  <p class="m-0 blue-grey-text text-darken-4 font-weight-700">{{$data_user->name}}</p>
                                  <p class="m-0 info-text">{{$data_user->phone}}</p>
                                </div>
                              </div>
                              <span class="option-icon">
                                <i class="material-icons">more_vert</i>
                              </span>
                            </div>
                            <!--/ Sidebar Header -->
                            <div class="sidebar-search animate fadeUp">
                              <div class="search-area">
                                <i class="material-icons search-icon">search</i>
                                <input type="text" placeholder="Search User" class="app-filter" id="chat_filter" onkeyup="search_connected_user('{{ Auth::id() }}', this.value);">
                              </div>
                              <div class="add-user">
                                <a href="#modal4" class="modal-trigger">
                                  <i class="material-icons mr-2 add-user-icon" >person_add</i>
                                </a>
                              </div>
                            </div>
                            <!-- Sidebar Content List -->
                            <div class="sidebar-content sidebar-chat" style="height: 20em">
                              <div class="card">
                                
                                <div class="card-body" id="user_list">
                                  
                                </div>
                              </div>
                            </div>
                            <!--/ Sidebar Content List -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--/ Sidebar Area -->
                  <!-- Content Area -->
                  <div class="chat-content-area animate fadeUp" id="chat_content_right">
                    <div class="chat-header" id="chat_header">
                      
                    </div>
                    <!--/ Chat header -->
                    <!-- Chat content area -->
                    <div class="chat-area" id="chat_scroll">
                      <div class="chats"  id="chat_area">
                       
                      </div>
                    </div>
                    <!--/ Chat content area -->
                    <!-- Chat footer <-->
                    <div class="chat-footer" id="chat_footer">
                      
                    </div>
                    <!--/ Chat footer -->
                  </div>
                  <!--/ Content Area -->
                </div>
              </div>
            </div>        
          </div>
        </div>
         
         
      </div>
   </div>

   <div id="modal1" class="modal modal-fixed-footer" style="">
    <div class="modal-content">
        <h4>Daftar Permintaan Chat</h4>
        <div class="row">
          <div class="col s12 m12 xl12">

              <div class="card-content">
                <ul class="list-group" id="notification_area">
                    
                </ul>
              </div>
              
            </div>
           </div>
        </div>
    </div>
    <div class="modal-footer">
    </div>
</div>

<div id="modal4" class="modal modal-fixed-footer">
    <div class="modal-content">
      <h4>Permintaan Chat</h4>
        <div class="row">
            <div class="col s12" id="show_detail">
              <div style="margin: 0 1em 0 1em;">
                <input type="text" class="form-control" placeholder="Search User..." autocomplete="off" id="search_people" onkeyup="search_user('{{ Auth::id() }}', this.value);" />
              </div>
              <div class="card-content">
                <div id="search_people_area" class="mt-3"></div>
              </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect waves-red btn-flat ">Close</a>
    </div>
</div>

<div style="bottom: 50px; right: 19px;" class="fixed-action-btn direction-top">
  <a class="btn-floating btn-large gradient-45deg-light-blue-cyan gradient-shadow modal-trigger notification" href="#modal1">
      <i class="material-icons">person_pin</i>
      <span class="" id="notif_add_user"></span>
  </a>
</div>
    
       
</div>

<style>

.notification{
  overflow:visible;
}

.notification:hover {
  background: red;
}

.notification .badge {
  position: absolute;
  line-height: 1.5 !important;
  top: 0px !important;
  width: 1.5rem !important;
  min-width: 0 !important;
  height: fit-content !important;
  right: 2.5rem !important;
  border-radius: 50%;
  background: red;
  color: white;
}

  .iconcilik{
    font-size: 1rem;
  }
  .text-muteds {
    font-size: 0.5em;
  }
  .chat-area
  {

    height: calc(100vh - 55vh) !important;
    /*overflow-y: scroll*/;
  }
  
  #chat_history
  {
    min-height: 30vh;
    display: flex;
    flex-direction: column;
  }
  .chat-area-cover {
    background: url({{ url('app-assets/images/gallery/chat-bg.jpg') }}) repeat scroll 0 0;
  }
</style>

<!-- END: Page Main-->
<script src="{{ url('app-assets/js/scripts/app-chat.js') }}"></script>

<script>
  $(function() {
    $('.chat-user').on( "click", function() {
      $('.chat-user').each(function() {
        $(this).removeClass('active');
      });
      $(this).addClass('active');
    });
  });
  var chatArea = document.getElementById('chat_content_right');

  function coverChatArea() {
    chatArea.classList.add('chat-area-cover');
  }

  function uncoverChatArea() {
    chatArea.classList.remove('chat-area-cover');
  }
  
  
  var hostname = window.location.hostname;
  var pointed_id =0;
  var user_friend = [];
  var user_friendpending = [];
  var tokendiv = document.getElementById('tokenid');
  var tokens = tokendiv.getAttribute('data-from-user-token');
  var conn = new WebSocket('ws://'+hostname+':8080/?token='+tokens);
  
  var websocketDiv = document.getElementById('websocket');
  var from_user_id = websocketDiv.getAttribute('data-from-user-id');
  
  var to_user_id = "";
  
  conn.onopen = function(e){
  
    $('#modal1').modal({
        dismissible: true,
        onOpenStart: function(modal,trigger) {
  
        },
        onOpenEnd: function(modal, trigger) {
            
        },
        onCloseEnd: function(modal, trigger){
            
        }
    });

    load_connected_chat_user(from_user_id);
  
    $('#modal4').modal({
        onOpenStart: function(modal,trigger) {
          load_unconnected_user(from_user_id);
        },
        onOpenEnd: function(modal, trigger) { 
        },
        onCloseEnd: function(modal, trigger){
            $('#search_people_area').empty();
        }
    });
    
  
    load_unread_notification(from_user_id);
    chatArea.classList.add('chat-area-cover');
  
  };
  
  conn.onmessage = function(e){
    
    var data = JSON.parse(e.data);
  
    if(data.image_link)
    {  
      document.getElementById('message_area').innerHTML = `<img src="{{ asset('images/`+data.image_link+`') }}" class="img-thumbnail img-fluid" />`;
    }
  
    if(data.status)
    {
      var online_status_icon = document.getElementsByClassName('online_status_icon');
  
      for(var count = 0; count < online_status_icon.length; count++)
      {
        if(online_status_icon[count].id == 'status_'+data.id)
        {
          if(data.status == 'Online')
          {
            online_status_icon[count].classList.add('text-success');
  
            online_status_icon[count].classList.remove('text-danger');
  
            document.getElementById('last_seen_'+data.id+'').innerHTML = 'Online';
          }
          else
          {
            online_status_icon[count].classList.add('text-danger');
  
            online_status_icon[count].classList.remove('text-success');
  
            document.getElementById('last_seen_'+data.id+'').innerHTML = data.last_seen;
          }
        }
      }
    }
  
    if(data.response_load_unconnected_user || data.response_search_user)
    {
      var html = '';
  
      if(data.data.length > 0)
      {
        html += `<table class="list-group">
                  <thead>
                    <tr>
                      <th rowspan="2" class="center-align">#</th>
                      <th rowspan="2" class="center-align">Nama</th>
                      <th rowspan="2" class="center-align">Send</th>
                    </tr>
                  </thead>
                  <tbody>`;
  
        for(var count = 0; count < data.data.length; count++)
        {
          var user_image = '';
          if(user_friend.includes(data.data[count].name)){

          }else{
            user_image = `<div class="chat-avatar">
                        <a class="avatar">
                          <img src="{{ url('/').'/' }}` + data.data[count].user_image + `" class="circle" alt="avatar">
                        </a>
                      </div>`;
            if(data.data[count].status == 'Offline'){
              var status = `<span class="task-cat red" style="margin-left: 1rem;top: -0.5rem !important; position:relative;">`+data.data[count].status+`</span>`
            }else{
              var status = `<span class="task-cat cyan" style="margin-left: 1rem;top: -0.5rem !important;position:relative;">`+data.data[count].status+`</span>`
            }
    
            html += `
            <tr id="last-row-item">
                <td class="center">
                    `+user_image+`
                </td>
                <td class="center" style='text-align:left !important'>
                  `+data.data[count].name+status+`
                </td>
                <td class="center">
                  <a class="mb-6 btn waves-effect waves-light green darken-1" onclick="send_request(this, ` + from_user_id + `, ` + data.data[count].id + `)">Request<i class="material-icons right">send</i></a>
                </td>
            </tr>
            `;
          }
          
        }
  
        html += `</tbody>
                </table>`;
      }
      else
      {
        html += `<table class="list-group">
                  <thead>
                    <tr>
                      <th class="center-align">Histori tidak ditemukan. Silahkan mulai percakapan</th>
                    </tr>
                  </thead>
                  </table>`;
      }
  
      document.getElementById('search_people_area').innerHTML = html;
    }
  
    if(data.response_from_user_chat_request)
    {
      search_user(from_user_id, document.getElementById('search_people').value);
  
      load_unread_notification(from_user_id);
    }
  
    if(data.response_to_user_chat_request)
    {
      load_unread_notification(data.user_id);
    }
  
    if(data.response_load_notification)
    {
      var html = '';
      var element = document.getElementById('notif_add_user');
      if(data.count_notification>0){
        element.classList.add('badge');
        element.innerHTML = data.count_notification;
      }else{
        element.classList.remove('badge');
        element.innerHTML='';
      } 
  
      for(var count = 0; count < data.data.length; count++)
      {
        var user_image = '';
 
        user_image = `<img src="{{ url('/').'/' }}`+data.data[count].user_image+`" width="40" class="rounded-circle" />`;
  
        html += `
        <li class="list-group-item">
          <div class="row" style="display: flex;">
            <div class="col col-8" style="align-self: center;">`+user_image+`&nbsp;`+data.data[count].name+`</div>
            <div class="col col-4" style="align-self: center;">
        `;
        if(data.data[count].notification_type == 'Send Request')
        {
          if(data.data[count].status == 'Pending')
          {
            html += `
            <span class=" users-view-status chip green lighten-5 green-text">Request Send</span>
          `;
          }
          else
          {
            html += ' <span class=" users-view-status chip red lighten-5 red-text">Request rejected</span>';
          }
        }
        else
        {
          if(data.data[count].status == 'Pending')
          {
            html += '<button type="button" class="mb-6 btn-floating waves-effect waves-light red darken-2" onclick="process_chat_request('+data.data[count].id+', '+data.data[count].from_user_id+', '+data.data[count].to_user_id+', `Reject`)"><i class="material-icons">clear</i></button>&nbsp;';
            html += '<button type="button" class="mb-6 btn-floating waves-effect waves-light green darken-1" onclick="process_chat_request('+data.data[count].id+', '+data.data[count].from_user_id+', '+data.data[count].to_user_id+', `Approve`)"><i class="material-icons">check</i></button>';
          }
          else
          {
            html += ' <span class=" users-view-status chip red lighten-5 red-text">Request rejected</span>';
          }
        }
  
        html += `
            </div>
          </div>
        </li>
        `;
      }
  
      document.getElementById('notification_area').innerHTML = html;
    }
  
    if(data.response_process_chat_request)
    {
      load_unread_notification(data.user_id);
  
      load_connected_chat_user(data.user_id);
    }
  
    if(data.response_connected_chat_user || data.response_search_connected_user)
    {
      var html = '<div class="chat-list">';
        
        
      if(data.data.length > 0)
      {
        for(var count = 0; count < data.data.length; count++)
        {

          html += `
          <a class="chat-user" onclick="make_chat_area(`+data.data[count].id+`, '`+data.data[count].name+`', '` + data.data[count].user_image + `'); load_chat_data(`+from_user_id+`, `+data.data[count].id+`); ">
          `;
          var last_seen = '';
          user_friend.push(data.data[count].name);
          if(data.data[count].user_status == 'Online')
          {
            last_seen = 'Online';
          }
          else
          {
            last_seen = data.data[count].last_seen;
          }
  
          var user_image = '';

          user_image = `<img src="{{ url('/').'/' }}`+data.data[count].user_image+`" class="circle z-depth-2 responsive-img" />`;
          
          html += `
                <div class="user-section">
                  <div class="row valign-wrapper">
                    <div class="col s2 media-image online pr-0">
                      `+user_image+`
                    </div>
                    <div class="col s10">
                      <p class="m-0 blue-grey-text text-darken-4 font-weight-700">`+data.data[count].name+`</p>
                      <p class="m-0 info-text" id="message_last`+data.data[count].id_message+`">`+data.data[count].last_chat+`</p>
                    </div>
                  </div>
                </div>
                <div class="info-section">
                  <div class="star-timing" style="width: fit-content !important;">
                    <div class="time">
                      <span><div class="text-right"><small class="text-muted last_seen" id="last_seen_`+data.data[count].id+`">`+last_seen+`</small></div></span>
                      <span class=" user_unread_message"  data-id="`+data.data[count].id+`" id="user_unread_message_`+data.data[count].id+`"></span>
                      </div>
                  </div>
                </div>
              </a>
          `;
        }
      }
      else
      {
        html += `<div class="user-section">
                  <div class="row valign-wrapper">
                    <div class="col s12 center-align pt-2 pb-2">
                      Percakapan tidak ditemukan.
                    </div>
                  </div>
                </div>`;
      }
  
      html += '</div>';
  
      document.getElementById('user_list').innerHTML = html;
  
      check_unread_message();
    }
    
    if(data.update_message_status)
    {
      var chat_status_element = document.querySelector('#chat_status_'+data.chat_message_id+'');

      if(chat_status_element)
      {
        if(data.update_message_status == 'Read')
        {
          chat_status_element.innerHTML = '<i class="material-icons dp48 iconcilik">done_all</i>';
        }
        if(data.update_message_status == 'Send')
        {
          chat_status_element.innerHTML = '<i class="material-icons dp48 iconcilik text-muted">done</i>';
        }
      }
  
      if(data.unread_msg)
      {
        var count_unread_message_element = document.getElementById('user_unread_message_'+data.from_user_id+'');
  
        if(count_unread_message_element)
        {
          var count_unread_message = count_unread_message_element.textContent;
          
          if(count_unread_message == '')	
          {
            count_unread_message = parseInt(0) + 1;
          }
          else
          {
            count_unread_message = parseInt(count_unread_message) + 1;
          }
  
          count_unread_message_element.innerHTML = '<span class="badge bg-danger rounded-pill">'+count_unread_message+'</span>';
        }
      }
    }

    if(data.message)
    {
      document.getElementById('chat_filter').value = '';
      load_connected_chat_user(from_user_id);
      var currentDate = new Date();
      var formattedTime = currentDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

      var html = '';
      if(data.from_user_id == from_user_id)
      {
  
        var icon_style = '';
  
        if(data.message_status == 'Not Send')
        {
          icon_style = '<span id="chat_status_'+data.chat_message_id+'" class="float-end"><i class="material-icons dp48 iconcilik">done</i></span>';
        }
        if(data.message_status == 'Send')
        {
          icon_style = '<span id="chat_status_'+data.chat_message_id+'" class="float-end"><i class="material-icons dp48 iconcilik text-muted">done</i></span>';
        }
  
        if(data.message_status == 'Read')
        {
          icon_style = '<span class="text-primary float-end" id="chat_status_'+data.chat_message_id+'"><i style="color:blue;" class="material-icons dp48 iconcilik">done_all</i></span>';
        }
  
        html += `
        <div class="chat chat-right">
          <div class="chat-body">
            <div class="chat-text" style="margin: 0 -1.5rem 0rem;">
              <p>`+icon_style+`&nbsp`+data.message+`&nbsp<small class="text-muteds text-right">` + formattedTime + `</small></p>
            </div>
          </div>
        </div>
        `;
      }
      else
      {
        if(to_user_id != '')
        {
          html += `
          <div class="chat chat">
            <div class="chat-body">
              <div class="chat-text" style="margin: 0 -1.5rem 0rem;">
                <p>`+data.message+`&nbsp<small class="text-muteds text-right">` + formattedTime + `</small></p>
              </div>
            </div>
          </div>
          `;
  
          update_message_status(data.chat_message_id, from_user_id, to_user_id, 'Read');
        }
        else
        {
          
          var count_unread_message_element = document.getElementById('user_unread_message_'+data.from_user_id+'');

          if(count_unread_message_element)
          {
            var count_unread_message = count_unread_message_element.textContent;
            if(count_unread_message == '')
            {
              count_unread_message = parseInt(0) + 1;
            }
            else
            {
              count_unread_message = parseInt(count_unread_message) + 1;
            }
            count_unread_message_element.innerHTML = '<span class="badge badge pill red white-text">'+count_unread_message+'</span>';

            update_message_status(data.chat_message_id, data.from_user_id, data.to_user_id, 'Send');
          }
        }
        
      }
  
      if(html != '')
      {
        
        if(from_user_id==data.to_user_id&&to_user_id==data.from_user_id){
          
          var previous_chat_element = document.querySelector('#chat_history');
          
          var chat_history_element = document.querySelector('#chat_history');

          chat_history_element.innerHTML = previous_chat_element.innerHTML + html;
        }else if(from_user_id==data.from_user_id&&to_user_id==data.to_user_id){
  
          var previous_chat_element = document.querySelector('#chat_history');
        
          var chat_history_element = document.querySelector('#chat_history');

          chat_history_element.innerHTML = previous_chat_element.innerHTML + html;
        }else{
          chat_history_element.innerHTML = previous_chat_element.innerHTML + html;
        }
        var chat_last_chat = document.querySelector('#message_last'+data.id_request_chat);
        
        chat_last_chat.innerHTML=data.message;
       
        scroll_top();
      }
      
    }
  
    if(data.chat_history)
    {
      var html = '', date = '', today = '{{ date('Y-m-d') }}', adaToday = false;
      var arrToday = today.split('-');

      for(var count = data.chat_history.length - 1; count >= 0; count--)
      {
        let arrDate = data.chat_history[count].created_at.split(' ');
        if((arrDate[0] !== date && date !== '') || (date == '')){
          let arrDateSplit = arrDate[0].split('-');
          html +=`
          <div class="chat" style="align-items: center !important;justify-content: center !important;">
            <div class="chat-body">
              <div class="center-align">
                - ` + getDisplayDate(arrDateSplit[0],arrDateSplit[1],arrDateSplit[2]) + ` -
              </div>
            </div>
          </div>
          `;
        }
        if(today == arrDate[0]){
          adaToday = true;
        }
        if(data.chat_history[count].from_user_id == from_user_id)
        {
          var icon_style = '';
          
          if(data.chat_history[count].message_status == 'Not Send')
          {
            icon_style = '<span id="chat_status_'+data.chat_history[count].id+'"><i class="material-icons dp48 iconcilik">done</i></span>';
          }
  
          if(data.chat_history[count].message_status == 'Send')
          {
            icon_style = '<span id="chat_status_'+data.chat_history[count].id+'" ><i class="material-icons dp48 iconcilik text-muted">done_all</i></span>';
          }
  
          if(data.chat_history[count].message_status == 'Read')
          {
            
            icon_style = '<span  id="chat_status_'+data.chat_history[count].id+'"><i class="material-icons dp48 iconcilik">done_all</i></span>';
          }

          html += `
            <div class="chat chat-right">
              <div class="chat-body">
                <div class="chat-text" style="margin: 0 -1.5rem 0rem;">
                  <p>` + icon_style +`&nbsp`+ data.chat_history[count].chat_message + `&nbsp<small class="text-muteds text-right">` + (new Date(data.chat_history[count].created_at)).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + `</small></p>
                </div>
              </div>
            </div>
          `;
        }
        else
        {
          if(data.chat_history[count].message_status != 'Read')
          {
            update_message_status(data.chat_history[count].id, data.chat_history[count].from_user_id, data.chat_history[count].to_user_id, 'Read');
          }
  
          html +=`
          <div class="chat">
            <div class="chat-body">
              <div class="chat-text" style="margin: 0 -1.5rem 0rem;">
                <p>`+data.chat_history[count].chat_message+`&nbsp<small class="text-muteds text-right">` + (new Date(data.chat_history[count].created_at)).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + `</small></p>
              </div>
            </div>
          </div>
          `;
  
          var count_unread_message_element = document.getElementById('user_unread_message_'+data.chat_history[count].from_user_id+'');
  
                  if(count_unread_message_element)
                  {
                    count_unread_message_element.innerHTML = '';
                  }
        }
        date = arrDate[0];
      }
      if(!adaToday){
        html +=`
          <div class="chat" style="align-items: center !important;justify-content: center !important;">
            <div class="chat-body">
              <div class="center-align">
                - ` + getDisplayDate(arrToday[0],arrToday[1],arrToday[2]) + ` -
              </div>
            </div>
          </div>
        `;
      }
  
      document.querySelector('#chat_history').innerHTML = html;
  
      scroll_top();
    }    
  };

  
  function scroll_top()
  {
      document.querySelector('#chat_scroll').scrollTop = 0;
      setTimeout(function() {
        document.querySelector('#chat_scroll').scrollTop = document.querySelector('#chat_scroll').scrollHeight;
      }, 100);
     
  }
  
  function load_unconnected_user(from_user_id)
  {
    var data = {
      from_user_id : from_user_id,
      type : 'request_load_unconnected_user'
    };
  
    conn.send(JSON.stringify(data));
  }
  
  function search_user(from_user_id, search_query)
  {
    if(search_query.length > 0)
    {
      var data = {
        from_user_id : from_user_id,
        search_query : search_query,
        type : 'request_search_user'
      };
  
      conn.send(JSON.stringify(data));
    }
    else
    {
      load_unconnected_user(from_user_id);
    }
  }

  function search_connected_user(from_user_id, search_query)
  {
    if(search_query.length > 0)
    {
      var data = {
        from_user_id : from_user_id,
        search_query : search_query,
        type : 'request_search_connected_user'
      };
  
      conn.send(JSON.stringify(data));
    }
    else
    {
      load_connected_chat_user(from_user_id);
    }
  }
  
  function send_request(element, from_user_id, to_user_id)
  {
    var data = {
      from_user_id : from_user_id,
      to_user_id : to_user_id,
      type : 'request_chat_user'
    };
  
    $(element).parent().parent().remove();

    swal({
        title: 'Sip!',
        text: 'Permintaan chat telah dikirimkan, mohon tunggu konfirmasi.',
        icon: 'success'
    });
  
    conn.send(JSON.stringify(data));
  }
  
  function load_unread_notification(user_id)
  {
    var data = {
      user_id : user_id,
      type : 'request_load_unread_notification'
    };
  
    conn.send(JSON.stringify(data));
  
  }
  
  function process_chat_request(chat_request_id, from_user_id, to_user_id, action)
  {
    var data = {
      chat_request_id : chat_request_id,
      from_user_id : from_user_id,
      to_user_id : to_user_id,
      action : action,
      type : 'request_process_chat_request'
    };
  
    conn.send(JSON.stringify(data));
  }
  
  function load_connected_chat_user(from_user_id)
  {
    var data = {
      from_user_id : from_user_id,
      type : 'request_connected_chat_user'
    };
  
    conn.send(JSON.stringify(data));
  }
  
  function make_chat_area(user_id, to_user_name, user_image)
  {
    close_chat();
    var footer= `
    <form action="javascript:void(0);" class="chat-input">
      <i class="material-icons mr-2">face</i>
      <i class="material-icons mr-2">attachment</i>
      <input id="message_area" class="form-control"  type="text" contenteditable style="border:1px solid #ccc; border-radius:5px;"></input>
      <a class="btn waves-effect waves-light send" id="send_button" onclick="send_chat_message()">Send</a>
    </form>
    `;
    var html = `
    <div class="chats" id="chat_history"></div>
    
    `;
  
    document.getElementById('chat_area').innerHTML = html;
    document.getElementById('chat_footer').innerHTML = footer;
    document.getElementById('chat_header').innerHTML = `
                <div class=" valign-wrapper">
                  <div class="col media-image online pr-0">
                    <img src="{{ url('/').'/' }}` + user_image + `" class="circle z-depth-2 responsive-img" />
                  </div>
                  <div class="col">
                    <p class="m-0 blue-grey-text text-darken-4 font-weight-700">`+to_user_name+`</p>
                    <p class="m-0 chat-text truncate">...</p>
                  </div>
                  
                </div>
                <span class="option-icon" align="right">
                    <span class="favorite">
                      <i class="material-icons">star_outline</i>
                    </span>
                    <a onclick="close_chat();">
                      <i class="material-icons mr-2 add-user-icon">delete</i>
                    </a>
                    <i class="material-icons"></i>
                    <i class="material-icons">more_vert</i>
                  </span>
                
    `;
  
    to_user_id = user_id;

    const input = document.getElementById('message_area');
    const button = document.getElementById('send_button');

    input.addEventListener('keyup', function(event) {
   
    if (event.keyCode === 13) {
      button.click();
    }
  });
    
  }
  
  function close_chat()
  {
    coverChatArea();
    document.getElementById('chat_header').innerHTML = '';
    document.getElementById('chat_area').innerHTML = '';
    document.getElementById('chat_footer').innerHTML = '';
    to_user_id = '';
  }
  
  function send_chat_message()
  {
    document.querySelector('#send_button').disabled = true;
  
    var message = document.getElementById('message_area').value;
  
    var data = {
      message : message,
      from_user_id : from_user_id,
      to_user_id : to_user_id,
      type : 'request_send_message'
    };
  
    conn.send(JSON.stringify(data));

    document.getElementById('message_area').value= '';
  
    document.querySelector('#send_button').disabled = false;
  }
  
  function load_chat_data(from_user_id, to_user_id)
  {
    uncoverChatArea();

    var data = {
      from_user_id : from_user_id,
      to_user_id : to_user_id,
      type : 'request_chat_history'
    };

    conn.send(JSON.stringify(data));
  }
  
  function update_message_status(chat_message_id, from_user_id, to_user_id, chat_message_status)
  {
    var data = {
      chat_message_id : chat_message_id,
      from_user_id : from_user_id,
      to_user_id : to_user_id,
      chat_message_status : chat_message_status,
      type : 'update_chat_status'
    };
   
    conn.send(JSON.stringify(data));
  }
  
  function check_unread_message()
  {
    var unread_element = document.getElementsByClassName('user_unread_message');
  
    for(var count = 0; count < unread_element.length; count++)
    {
      var temp_user_id = unread_element[count].dataset.id;
  
      var data = {
        from_user_id : from_user_id,
        to_user_id : temp_user_id,
        type : 'check_unread_message'
      };
  
      conn.send(JSON.stringify(data));
    }
  }

  function getDisplayDate(year, month, day) {
      today = new Date();
      today.setHours(0);
      today.setMinutes(0);
      today.setSeconds(0);
      today.setMilliseconds(0);
      compDate = new Date(year,month-1,day);
      diff = today.getTime() - compDate.getTime();
      if (compDate.getTime() == today.getTime()) {
          return "Hari ini";
      } else if (diff <= (24 * 60 * 60 *1000)) {
          return "Kemarin";
      } else { 
          return compDate.toLocaleDateString('en-GB');
      }
  }
  
</script>