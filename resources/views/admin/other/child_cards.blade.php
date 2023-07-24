@foreach($childRows as $index =>$childRow)
    @if (count($childRow["child"]) == 0)
        <div class="card border-radius-3 fadeLeft child-card col s12 m5 l3" data-parent-id="{{ $childRow["parent_id"] }}" data-child-id="{{ $childRow["id"] }}" style="padding: 1.5rem !important; margin-left: 1rem !important; margin-right: 0.5rem !important;min-width:17rem" onclick="goto('{{$childRow["url"] }}')">
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
            
            <div class="card-content center ">
                <i class="material-icons background-round mt-5 mb-5 gradient-shadow gradient-45deg-amber-amber white-text">{{$childRow["icon"]}}</i>
                <h6 class="mb-0">{{$childRow["name"]}}</h6>
                
            </div>
            
            
            
        </div>
    @else
        <div class="card border-radius-3 fadeLeft child-card col s12 m5 l3" data-parent-id="{{ $childRow["parent_id"] }}" data-child-id="{{ $childRow["id"] }}" style="padding: 1.5rem !important; margin-left: 1rem !important; margin-right: 0.5rem !important;min-width:17rem">
            <div class="row" style="display: flex; justify-content: space-between;">
                @if ($childRow["is_new"]=="1")
                    <div class="col s12 m6 l6" style="display: flex">
                        <span class="badge badge pill green float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Baru!" style="height: 30px !important; margin-top: 7px;">
                            <i class="material-icons" style="margin-right: 0rem !important; width: auto !important; padding: 2px 0px 2px 0px !important; margin-top: 4px;">flag</i>
                        </span>
                    </div>
                @endif
                @if ($childRow["is_maintenance"]=="1")
                    <div class="col s12 m6 l6" >
                        <span class="badge badge pill red float-right mr-7 tooltipped" data-position="bottom" data-tooltip="Sedang Dalam Perbaikan" style="height: 30px !important; margin-top: 7px;">
                            <i class="material-icons" style="margin-right: 0rem !important; width: auto !important; padding: 2px 0px 2px 0px !important; margin-top: 4px;">build</i>
                        </span>
                    </div>
                @endif
            </div>
            
                <div class="card-content center">
                    <i class="material-icons background-round mt-5 mb-5 gradient-shadow gradient-45deg-amber-amber white-text">{{$childRow["icon"]}}</i>
                    <h6 class="mb-0">{{$childRow["name"]}}</h6>
                    
                </div>
            
        </div>
    @endif
    @if(count($childRow["child"]) > 0)
        <div class="child-cards_{{ $childRow["id"] }} child-cards row"  style="display: none;" >
            @include('admin.other.child_cards', ['childRows' => $childRow["child"],'url'=>''])
        </div>
    @else
        <div class="child-cards_{{ $childRow["id"] }} child-cards row"  style="display: none;" >
            @include('admin.other.child_cards', ['childRows' => $childRow["child"], 'url' => url('admin/' . $childRow["url"])])
        </div>
    @endif
@endforeach
