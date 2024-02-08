<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\CustomHelper;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Http\Controllers\Controller;
use App\Models\GoodIssue;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\PurchaseDownPayment;
use App\Models\User;

class DashboardController extends Controller
{
    protected $user;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->user = $user;
    }
    public function index()
    {
        $data = [
            'title'         => 'Dashboard',
            'content'       => 'admin.dashboard.main',
            /* 'itemcogs'      => ItemCogs::orderByDesc('date')->orderByDesc('id')->get(), */
            'itemstocks'    => ItemStock::where('qty','>',0)->get(),
            'user'          => $this->user,
        ];
    
        return view('admin.layouts.index', ['data' => $data]);

        /* $gr = GoodReceiptDetail::all();
        $gi = GoodIssueDetail::all();
        $grcv = GoodReceiveDetail::all(); */

        /* foreach($gr as $row){
            $data = ItemStock::where('place_id',$row->place_id)->where('warehouse_id',$row->warehouse_id)->where('item_id',$row->item_id)->first();
            if($data){
                $data->update([
                    'qty' => $data->qty + $row->qty,
                ]);
            }else{
                ItemStock::create([
                    'place_id'      => $row->place_id,
                    'warehouse_id'  => $row->warehouse_id,
                    'item_id'       => $row->item_id,
                    'qty'           => $row->qty,
                ]);
            }
        } */

        /* foreach($gi as $row){
            $dataupdate = ItemStock::find($row->item_stock_id);
            $dataupdate->update([
                'qty'   => $dataupdate->qty - $row->qty,
            ]);
        }
        
        foreach($grcv as $row){
            $dataupdate = ItemStock::where('place_id',$row->place_id)->where('warehouse_id',$row->warehouse_id)->where('item_id',$row->item_id)->first();
            $dataupdate->update([
                'qty'   => $dataupdate->qty + $row->qty,
            ]);
        } */

        /* $gr = GoodReceipt::all();
        $gi = GoodIssue::all();
        $grcv = GoodReceive::all();

        $data = [];

        foreach($gr as $row){
            $data[] = [
                'type'          => 'IN',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        foreach($gi as $row){
            $data[] = [
                'type'          => 'OUT',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        foreach($grcv as $row){
            $data[] = [
                'type'          => 'IN',
                'date'          => $row->post_date,
                'lookable_type' => $row->getTable(),
                'lookable_id'   => $row->id,
            ];
        }

        $collection = collect($data)->sortBy(function($item) {
                        return [$item['date'], $item['type']];
                    })->values();

        foreach($collection as $row){
            CustomHelper::sendJournal($row['lookable_type'],$row['lookable_id']);
        } */

        /* $podp = PurchaseDownPayment::all();
        foreach($podp as $row){
            CustomHelper::sendJournal($row->getTable(),$row->id,$row->account_id);
        } */
    }
}
