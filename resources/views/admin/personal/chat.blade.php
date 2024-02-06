<style>
  .circle{
    width:50px !important;
  }
  
  .chat-application .app-chat .chat-content .chat-content-area .chat-area .chats .chat {
    margin: 0.3rem 0.5rem;
  }

  .chat-information-right {
    position:relative;
    bottom:-10px;
    color: grey;
    right: 10px;
    font-size:0.9rem;
  }

  .chat-information-left {
    position:relative;
    bottom:-10px;
    color: grey;
    left:10px;
    font-size:0.9rem;
  }
</style>
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/app-chat.css') }}">
<div id="main">
  <div class="row">
    <div class="content-wrapper-before gradient-45deg-indigo-blue"></div>
      <div class="col s12">
        <div class="container">
          <div class="chat-application">
            <div class="chat-content-head">
              <div class="header-details">
                <h5 class="m-0 sidebar-title" style="color:white;"><i class="material-icons app-header-icon text-top">chat_bubble_outline</i> Obrolan</h5>
              </div>
            </div>
            <div class="app-chat">
              <div class="content-area content-right">
                <div class="app-wrapper">
                  <!-- Sidebar menu for small screen -->
                  <a href="#" data-target="chat-sidenav" class="sidenav-trigger hide-on-large-only">
                    <i class="material-icons">menu</i>
                  </a>
                  <!--/ Sidebar menu for small screen -->

                  <div class="card card card-default scrollspy border-radius-6 fixed-width">
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
                                      <img src="../../../app-assets/images/user/12.jpg" alt=""
                                        class="circle z-depth-2 responsive-img">
                                    </div>
                                    <div class="col s10">
                                      <p class="m-0 blue-grey-text text-darken-4 font-weight-700">Lawrence Collins</p>
                                      <p class="m-0 info-text">Apple pie bonbon cheesecake tiramisu</p>
                                    </div>
                                  </div>
                                  <span class="option-icon">
                                    <i class="material-icons">more_vert</i>
                                  </span>
                                </div>
                                <!--/ Sidebar Header -->

                                <!-- Sidebar Search -->
                                <div class="sidebar-search animate fadeUp">
                                  <div class="search-area">
                                    <i class="material-icons search-icon">search</i>
                                    <input type="text" placeholder="Search Chat" class="app-filter" id="chat_filter">
                                  </div>
                                  <div class="add-user">
                                    <a href="#">
                                      <i class="material-icons mr-2 add-user-icon">person_add</i>
                                    </a>
                                  </div>
                                </div>
                                <!--/ Sidebar Search -->

                                <!-- Sidebar Content List -->
                                <div class="sidebar-content sidebar-chat">
                                  <div class="chat-list">
                                    
                                  </div>
                                  <div class="no-data-found">
                                    <h6 class="center">Chat tidak ditemukan</h6>
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
                      <div class="chat-content-area animate fadeUp hide">
                        <!-- Chat header -->
                        <div class="chat-header">
                          <div class="row valign-wrapper">
                            <div class="col media-image online pr-0">
                              <img src="" alt="" class="circle z-depth-2 responsive-img" id="imageTarget">
                            </div>
                            <div class="col">
                              <p class="m-0 blue-grey-text text-darken-4 font-weight-700" id="nameTarget"></p>
                              <p class="m-0 chat-text truncate">-</p>
                            </div>
                          </div>
                          <span class="option-icon">
                            <span class="favorite">
                              <i class="material-icons">star_outline</i>
                            </span>
                            <i class="material-icons">delete</i>
                            <i class="material-icons">more_vert</i>
                          </span>
                        </div>
                        <!--/ Chat header -->

                        <!-- Chat content area -->
                        <div class="chat-area">
                          <div class="chats">
                            <div class="chats" id="chatTarget">

                            </div>
                          </div>
                        </div>
                        <!--/ Chat content area -->

                        <!-- Chat footer <-->
                        <div class="chat-footer">
                          <form onsubmit="enterChat();" action="javascript:void(0);" class="chat-input">
                            <i class="material-icons mr-2">face</i>
                            <i class="material-icons mr-2">attachment</i>
                            <input type="text" placeholder="Type message here.." class="message mb-0" id="main-text">
                            <a class="btn waves-effect waves-light send" onclick="enterChat();">Send</a>
                          </form>
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
      <div class="content-overlay"></div>
    </div>
  </div>
</div>
<!-- END: Page Main-->
<script src="{{ url('app-assets/js/scripts/app-chat.js') }}"></script>
<script>
  $(function() {
    sync();
    setInterval(function () {
      sync();
    },10000);
  });

  function loadMessage(code){
    $.ajax({
      url: '{{ Request::url() }}/get_message',
      type: 'POST',
      dataType: 'JSON',
      data: {
        code : code,
      },
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        loadingOpen('.chat-content');
      },
      success: function(response) {
        loadingClose('.chat-content');
        if(!$('#chatUser' + code).hasClass('active')){
          $('#chatUser' + code).addClass('active');
        }
        $('#imageTarget').attr('src',$('#imageSource' + code).attr('src'));
        $('#nameTarget').text($('#nameSource' + code).text());
        $('.chat-content-area').removeClass('hide');
        $('#chatTarget').empty();
        $.each(response.data, function(i, val) {
          $('#chatTarget').append(`
            <div class="chat ` + (val.is_me ? 'chat-right' : '') + `" data-id="` + val.id + `">
              <div class="chat-avatar">
                <a class="avatar">
                  <img src="` + val.photo + `" class="circle" alt="avatar" />
                </a>
              </div>
              <div class="chat-body">
                <div class="chat-text">
                  <p>` + val.message + `</p>
                  <div class="` + (val.is_me ? 'chat-information-right' : 'chat-information-left') + `">` + val.time + `</div>
                </div>
              </div>
            </div>
          `);
        });
        $(".chat-area").scrollTop($(".chat-area > .chats").height());
      },
      error: function() {
          loadingClose('.chat-content');
          swal({
              title: 'Ups!',
              text: 'Check your internet connection.',
              icon: 'error'
          });
      }
    });
  }

  function refreshChat(){
    if($('.chat-user.active').length > 0){
      let code = $('.chat-user.active').data('code');
      $.ajax({
        url: '{{ Request::url() }}/refresh',
        type: 'POST',
        dataType: 'JSON',
        data: {
          code : code,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
        },
        success: function(response) {
          $.each(response.data, function(i, val) {
            let there = false;

            if($('.chat[data-id="' + val.id + '"]').length > 0){
              there = true;
            }

            if(!there){
              $('#chatTarget').append(`
                <div class="chat ` + (val.is_me ? 'chat-right' : '') + `" data-id="` + val.id + `">
                  <div class="chat-avatar">
                    <a class="avatar">
                      <img src="` + val.photo + `" class="circle" alt="avatar" />
                    </a>
                  </div>
                  <div class="chat-body">
                    <div class="chat-text">
                      <p>` + val.message + `</p>
                      <div class="` + (val.is_me ? 'chat-information-right' : 'chat-information-left') + `">` + val.time + `</div>
                    </div>
                  </div>
                </div>
              `);
            }
            $(".chat-area").scrollTop($(".chat-area > .chats").height());
          });
        },
        error: function() {
            swal({
                title: 'Ups!',
                text: 'Check your internet connection.',
                icon: 'error'
            });
        }
      });
    }
    sync();
  }

  function enterChat() {
    if($('.chat-user.active').length > 0){
      var message = $(".message").val();
      if (message != "") {
          let code = $('.chat-user.active').data('code');
          $.ajax({
            url: '{{ Request::url() }}/send',
            type: 'POST',
            dataType: 'JSON',
            data: {
              code : code,
              message : message,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
              loadingOpen('.chat-area');
            },
            success: function(response) {
              loadingClose('.chat-area');
              
              $('#main-text').val('');
              refreshChat();
            },
          error: function() {
              loadingClose('.chat-area');
              swal({
                  title: 'Ups!',
                  text: 'Check your internet connection.',
                  icon: 'error'
              });
          }
        });
      }
    }
  }

  function sync(){
    let code = '';
    if($('.chat-user.active').length > 0){
      code = $('.chat-user.active').data('code');
    }
    $.ajax({
      url: '{{ Request::url() }}/sync',
      type: 'POST',
      dataType: 'JSON',
      data: {
          
      },
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function() {
        
      },
      success: function(response) {

        $('.chat-list').empty();
        $(".no-data-found").removeClass('show');
        if(response.data.length > 0){

          $.each(response.data, function(i, val) {
            $('.chat-list').append(`
              <div class="chat-user ` + (code == val.code ? 'active' : '') + `" id="chatUser` + val.code + `" data-code="` + val.code + `">
                <div class="user-section">
                  <div class="row valign-wrapper">
                    <div class="col s2 media-image online pr-0">
                      <img src="` + val.photo + `" alt=""
                        class="circle z-depth-2 responsive-img" id="imageSource` + val.code + `">
                    </div>
                    <div class="col s10">
                      <p class="m-0 blue-grey-text text-darken-4 font-weight-700" id="nameSource` + val.code + `">` + val.name + `</p>
                      <p class="m-0 info-text">` + val.last_message + `</p>
                    </div>
                  </div>
                </div>
                <div class="info-section">
                  <div class="star-timing">
                    <div class="time">
                      <span>` + val.last_time + `</span>
                    </div>
                  </div>
                </div>
              </div>
            `);

            if(code){
              refreshChat();
            }
          });
          $(".chat-user").on("click", function () {
            loadMessage($(this).data('code'));
          });
        }else{
          if (!$(".no-data-found").hasClass('show')) {
              $(".no-data-found").addClass('show');
          }
        }
      },
      error: function() {
          swal({
              title: 'Ups!',
              text: 'Check your internet connection.',
              icon: 'error'
          });
      }
    });
  }
</script>