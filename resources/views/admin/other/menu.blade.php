<style>
    .hover-shadow {
        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.1), 0 2px 10px 0 rgba(0, 0, 0, 0.08) !important;
        transition: box-shadow 0.3s ease;
    }
</style> 
<link rel="stylesheet" type="text/css" href="{{ url('app-assets/css/pages/page-timeline.css') }}">
<div id="main">
    <div class="row">
        <div class="pt-3 pb-1" id="breadcrumbs-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col s8 m6 l6">
                        <h5 class="breadcrumbs-title mt-0 mb-0"><span>{{ $title }}</span></h5>
                        <ol class="breadcrumbs mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ Str::title(str_replace('_',' ',Request::segment(2))) }}</a>
                            </li>
                        </ol>
                    </div>
                    <div class="col s4 m6 l6">
                        {{-- <a class="btn btn-small waves-effect waves-light breadcrumbs-btn right mr-3" href="javascript:void(0);" onclick="refresh();">
                            <i class="material-icons hide-on-med-and-up">refresh</i>
                            <span class="hide-on-small-onl">Refresh</span>
                            <i class="material-icons right">refresh</i>
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="col s12">
           
            <div class="row">
                @foreach($menu as $index =>$row)
                
                <div class="col s12 m6 l4 parent-null" style="margin:0">
                    <div class="card  border-radius-3 fadeLeft parent-card" data-parent-id="{{ $row["id"] }}" style="min-height: 13rem;max-height: 13rem;" >
                        <div class="row" style="display: flex; justify-content: space-between;min-height:2rem;max-height: 2rem;">
                            
                                <div class="col s12 m6 l6" style="display: flex">
                                    @if ($row["is_new"]=="1")
                                    <span class="badge badge pill green float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Baru!" style="height: 30px !important; margin-top: 7px;">
                                        <i class="material-icons" style="margin-right: 0rem !important; width: auto !important; padding: 2px 0px 2px 0px !important; margin-top: 4px;">flag</i>
                                    </span>
                                    @endif
                                </div>
                                <div class="col s12 m6 l6" >
                                    @if ($row["is_maintenance"]=="1")
                                    <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height: 30px !important; margin-top: 7px;">
                                        <i class="material-icons" style="margin-right: 0rem !important; width: auto !important; padding: 2px 0px 2px 0px !important; margin-top: 4px;">build</i>
                                    </span>
                                    @endif
                                </div>
                            
                        </div>
                        <div class="card-content center">
                            <i class="material-icons background-round mt-5 mb-5 gradient-shadow gradient-45deg-amber-amber white-text">{{$row["icon"]}}</i>
                            <h6 class="mb-0">{{$row["name"]}}</h6>
                        </div>
                    </div>
     
                    
                </div>
                <div class="col child-cards_{{ $row["id"] }} child-cards row"  style="display: none;padding-left:10rem;" data-parent-id="{{ $row["id"] }}">
                    @include('admin.other.child_cards', ['childRows' => $row["child"],'url'=>""])
                </div>
                @endforeach  
            </div>
            
            <div class="content-overlay"></div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    const parentCards = document.querySelectorAll('.parent-card');


    parentCards.forEach((card) => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('gradient-shadow');
            card.classList.add('gradient-45deg-light-blue-cyan');
        });

        card.addEventListener('mouseleave', () => {
            card.classList.remove('gradient-shadow');
            card.classList.remove('gradient-45deg-light-blue-cyan');
        });
    });

    const childMenu = document.querySelectorAll('.child-card');


    childMenu.forEach((card) => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('gradient-shadow');
            card.classList.add('gradient-45deg-light-blue-cyan');
        });

        card.addEventListener('mouseleave', () => {
            card.classList.remove('gradient-shadow');
            card.classList.remove('gradient-45deg-light-blue-cyan');
        });
    });
    $('.parent-card').click(function() {
        const parentId = $(this).data('parent-id');
        const childCards = $(`.child-cards_${parentId}`);
        $('.child-cards').not(childCards).hide();
        $('.parent-null').hide();
      
        childCards.slideToggle();
    });
    $('.child-card').click(function() {
        const childCards = $(this).closest('.child-cards').find('.child-card');

        if (childCards.length === 0) {
            const url = $(this).data('url');
        
            window.location.href = url;
        } else {
            const parentId = $(this).data('child-id');
            const childCards = $(`.child-card[data-parent-id="${parentId}"]`);
            console.log(childCards);
          
            $('.child-card').not(childCards).slideUp();
            const childCardse = $(`.child-cards_${parentId}`);
            childCardse.slideToggle();
      
            childCards.show();
        }
    });
});

function goto(url){
    window.location = url;
}

function refresh(){

}

</script>




