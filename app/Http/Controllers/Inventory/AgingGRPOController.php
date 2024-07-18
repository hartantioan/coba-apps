<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportAgingGoodReceipt;
use App\Models\User;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\Warehouse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AgingGRPOController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
        $data = [
            'title'     => 'Laporan Aging GRPO',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.aging_good_receipt',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }


    public function export(Request $request){
        $plant = $request->plant? $request->plant : '';
        $item = $request->item ? $request->item : '';
        $warehouse = $request->warehouse ? $request->warehouse : '';
		$group = $request->group ?? '';
        $date = $request->date ?? '';
        $start_date = $request->start_date ?? '';
        $end_date = $request->end_date ?? '';
		return Excel::download(new ExportAgingGoodReceipt($plant,$item,$warehouse,$group,$date,$start_date,$end_date), 'aging_grpo_'.uniqid().'.xlsx');
    }

    function dateDiffInDays($date1, $date2) {
    
        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);
      
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return round($diff / 86400);
    }
}
