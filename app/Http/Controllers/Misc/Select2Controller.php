<?php

namespace App\Http\Controllers\Misc;

use App\Helpers\CustomHelper;
use App\Models\ApprovalStage;
use App\Models\AttendancePeriod;
use App\Models\CostDistribution;
use App\Models\Department;
use App\Models\FundRequest;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodScaleDetail;
use App\Models\HardwareItem;
use App\Models\HardwareItemGroup;
use App\Models\InventoryTransferOut;
use App\Models\ItemStock;
use App\Models\Line;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderPlan;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\PaymentRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrderDetail;
use App\Models\ReceptionHardwareItemsUsage;
use App\Models\Region;
use App\Models\Place;
use App\Models\Shift;
use App\Models\Transportation;
use App\Models\Warehouse;
use App\Models\Country;
use App\Models\Item;
use App\Models\Asset;
use App\Models\Unit;
use App\Models\Coa;
use App\Models\User;
use App\Models\Bank;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Equipment;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class Select2Controller extends Controller {
    
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    
    public function city(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 5")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'subdistrict'   => $d->getSubdistrict(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function district(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 8")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function subdistrict(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 13")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function province(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("LENGTH(code) = 2")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'cities'        => $d->getCity(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function region(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function country(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Country::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function item(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coa(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                /* })->whereDoesntHave('childSub') */
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNull('is_hidden')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'uom'           => '-',
                'code'          => CustomHelper::encrypt($d->code),
                'type'          => $d->getTable(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaCashBank(Request $request)
    {
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->where('code','like',"100.01.01%")
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function rawCoa(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $pre_text = str_repeat(" - ", $d->level);
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $pre_text.($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function employee(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' => $d->name.' - '.$d->phone.' Pos. '.($d->position ? $d->position->name : 'N/A').' Dep. '.($d->department ? $d->department->name : 'N/A'),
                'limit_credit'  => $d->limit_credit,
                'count_limit'   => $d->count_limit_credit,
                'balance_limit' => $d->limit_credit - $d->count_limit_credit,
                'arrinfo'       => $d
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function employeeCustomer(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->whereIn('type',['1','2'])
                ->where('status','1')->orderBy('type')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name.' - '.$d->type(),
                'type'          => $d->type,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_purchase_item');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'buy_unit'          => $d->buyUnit->code,
                'old_prices'        => $d->oldPrices($this->dataplaces),
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function salesItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_sales_item');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'sell_unit'         => $d->sellUnit->code,
                'old_prices'        => $d->oldSalePrices($this->dataplaces),
                'stock_list'        => $d->currentStockSales($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function assetItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where(function($query) use($search){
                    $query->whereNotNull('is_asset');
                })->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'code'          => $d->code,
                'name'          => $d->name,
                'uom'           => $d->uomUnit->code,
                'buy_unit'      => $d->buyUnit->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function asset(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,3,',','.') : '0,000',
                'price'                 => $capital ? number_format($capital->price,3,',','.') : '0,000',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function assetCapitalization(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereNull('date')
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,3,',','.') : '0,000',
                'price'                 => $capital ? number_format($capital->price,3,',','.') : '0,000',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function assetRetirement(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Asset::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('book_balance','>',0)
                ->get();

        foreach($data as $d) {
            $capital = $d->getUnitFromCapitalization();
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->code.' - '.$d->name,
                'code'                  => $d->code,
                'name'                  => $d->name,
                'unit_name'             => $capital ? $capital->unit->name : '',
                'unit_id'               => $capital ? $capital->unit_id : '',
                'nominal'               => $d->nominal > 0 ? number_format($d->nominal,3,',','.') : '0,000',
                'price'                 => $capital ? number_format($capital->price,3,',','.') : '0,000',
                'place_id'              => $d->place_id,
                'place_name'            => $d->place->name,
                'book_balance'          => $d->book_balance,
                'qty_balance'           => $d->qtyBalance(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function supplier(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','3')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name,
                'top'           => $d->top,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function customer(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','2')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			        => $d->id,
                'text' 			        => $d->employee_no.' - '.$d->name,
                'top_customer'          => $d->top,
                'top_internal'          => $d->top_internal,
                'limit_credit'          => number_format($d->limit_credit,2,',','.'),
                'count_limit_credit'    => number_format($d->count_limit_credit,2,',','.'),
                'billing_address'       => $d->getBillingAddress()
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseRequest(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseRequestDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalance()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function goodIssue(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = GoodIssue::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodIssueDetail',function($query) use($search){
                                $query->whereHas('itemStock',function($query) use($search){
                                    $query->whereHas('item',function($query) use($search){
                                        $query->where('code', 'like', "%$search%")
                                            ->orWhere('name','like',"%$search%");
                                    });
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalance()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseOrder(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseOrder::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('supplier',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('purchaseOrderDetail',function($query){
                    $query->whereIn('place_id',$this->dataplaces);
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])
                ->where('inventory_type','1')->get();

        foreach($data as $d) {
            if($d->hasBalance()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function warehouse(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Warehouse::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->whereIn('id',$this->datawarehouses)
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function vendor(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->where('type','4')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function businessPartner(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')->orderBy('type')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->employee_no.' - '.$d->name.' - '.$d->type(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function goodReceipt(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = GoodReceipt::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function goodReceiptReturn(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = GoodReceipt::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiptDetail',function($query) use($search){
                                $query->whereHas('item',function($query) use($search){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere;
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('goodReceiptDetail',function($query) use($search){
                    $query->whereDoesntHave('purchaseInvoiceDetail');
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceReturn()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function supplierVendor(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = User::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('employee_no', 'like', "%$search%")
                    ->orWhere('username', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%")
                    ->orWhere('pic', 'like', "%$search%")
                    ->orWhere('pic_no', 'like', "%$search%")
                    ->orWhere('office_no', 'like', "%$search%");
                })
                ->where('status','1')
                ->whereIn('type',['3','4'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function bank(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Bank::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function project(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Project::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function unit(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Unit::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function paymentRequest(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = PaymentRequest::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                })
                ->whereDoesntHave('outgoingPayment')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
                'admin'         => number_format($d->admin,3,',','.'),
                'grandtotal'    => number_format($d->grandtotal,3,',','.'),
                'code'          => $d->code
            ];
        }

        return response()->json(['items' => $response]);
    }
 
    public function equipment(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Equipment::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function workOrder(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = WorkOrder::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhereHas('equipment',function($query) use($search){
                        $query->where('name','like',"%$search%");
                    })
                    ->orWhereHas('user',function($query) use($search){
                        $query->where('name','like',"%$search%");
                    });
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'equipment'     => $d->equipment->id
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function approvalStage(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = ApprovalStage::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->approval->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function menu(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Menu::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%")
                    ->orWhere('url','like',"%$search%")
                    ->orWhere('table_name','like',"$search");
                })
                ->where('status','1')
                ->whereDoesntHave('sub')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->fullName(),
                'hasGrandtotal' => $d->hasGrandtotal() ? '1' : '0',
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function department(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Department::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function groupHardwareItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = HardwareItemGroup::where(function($query) use($search){
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function hardwareItem(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = HardwareItem::where(function($query) use($search){
                    $query->orWhere('code','like',"%$search%");
                })
                ->whereHas('item', function ($query) use ($search) {
                    $query->where('name','like',"%$search%");
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->item->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function hardwareItemForReception(Request $request)
    {
        $response = [];
        
        $search   = $request->search;
        $excludedIds = ReceptionHardwareItemsUsage::pluck('hardware_item_id')->toArray();
        $data = HardwareItem::where(function ($query) use ($search) {
                    $query->orWhere('code', 'like', "%$search%");
                })
                ->whereHas('item', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->where('status', '1')
                ->doesntHave('receptionHardwareItemsUsage')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.'-'.$d->item->name,
            ];
        }
        return response()->json(['items' => $response]);
    }

    public function itemForHardware(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $itemIdsWithConnection = HardwareItem::pluck('item_id')->toArray();
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })->where('status','1')
                ->whereNotIn('id', $itemIdsWithConnection)
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStock($this->dataplaces,$this->datawarehouses),
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function coaJournal(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('prefix', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNotNull('show_journal')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> ($d->prefix ? $d->prefix.' ' : '').''.$d->code.' - '.$d->name,
                'must_bp'       => $d->bp_journal ? '1' : '',
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function fundRequestBs(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = FundRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('fundRequestDetail',function($query) use($search){
                                $query->where('note', 'like', "%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('hasPaymentRequestDetail',function($query){
                    $query->whereHas('paymentRequest',function($query){
                        $query->whereHas('outgoingPayment');
                    });
                })
                ->where('type','1')
                /* ->whereDoesntHave('used') */
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $balance = $d->balanceCloseBill();
            if($balance > 0 && $d->document_status == '2'){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($balance,2,',','.'),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function fundRequestBsClose(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = FundRequest::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhereHas('fundRequestDetail',function($query) use($search){
                                $query->where('note', 'like', "%$search%");
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereHas('hasPaymentRequestDetail',function($query){
                    $query->whereHas('paymentRequest',function($query){
                        $query->whereHas('outgoingPayment');
                    });
                })
                ->where('type','1')
                /* ->whereDoesntHave('used') */
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $balance = $d->balanceCloseBill();
            if($balance > 0 && $d->document_status == '3'){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($balance,2,',','.'),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseInvoice(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseInvoice::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note.' - Saldo '.number_format($d->balance,2,',','.'),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseDownPayment(Request $request)
    {

        $response = [];
        $search   = $request->search;
        $data = PurchaseDownPayment::where(function($query) use($search){
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('due_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search){
                                $query->whereHas('purchaseOrder',function($query) use($search){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                })
                ->whereDoesntHave('used')
                ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->note,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function costDistribution(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = CostDistribution::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                 })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $details = [];

            foreach($d->costDistributionDetail as $row){
                $details[] = [
                    'id'                => $row->id,
                    'place_id'          => $row->place_id,
                    'place_name'        => $row->place_id ? $row->place->code : '',
                    'line_id'           => $row->line_id ? $row->line_id : '',
                    'line_name'         => $row->line_id ? $row->line->code.' - '.$row->line->name : '',
                    'machine_id'        => $row->machine()->exists ? $row->machine_id : '',
                    'machine_name'      => $row->machine()->exists ? $row->machine->name : '',
                    'department_id'     => $row->department_id ? $row->department_id : '',
                    'department_name'   => $row->department_id ? $row->department->name : '',
                    'warehouse_id'      => $row->warehouse_id ? $row->warehouse_id : '',
                    'warehouse_name'    => $row->warehouse_id ? $row->warehouse->name : '',
                    'percentage'        => $row->percentage,
                ];
            }

            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'coa_id'            => $d->coa_id ? $d->coa_id : '',
                'coa_name'          => $d->coa_id ? ($d->coa->prefix ? $d->coa->prefix.' ' : '').''.$d->coa->code.' - '.$d->coa->name : '',
                'details'           => $details,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function line(Request $request)
    {   
        $response = [];
        $search   = $request->search;
        $data = Line::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%");
                 })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function itemTransfer(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $place      = $request->place;
        $warehouse  = $request->warehouse;
        $data = Item::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('name', 'like', "%$search%");
                })
                ->whereHas('itemStock',function($query)use($place,$warehouse){
                    $query->where('place_id',$place)
                        ->where('warehouse_id',$warehouse);
                })
                ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'code'              => $d->code,
                'name'              => $d->name,
                'uom'               => $d->uomUnit->code,
                'price_list'        => $d->currentCogs($this->dataplaces),
                'stock_list'        => $d->currentStockPlaceWarehouse($place,$warehouse),
                'list_warehouse'    => $d->warehouseList(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function inventoryTransferOut(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = InventoryTransferOut::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('note', 'like', "%$search%")
                    ->orWhereHas('inventoryTransferOutDetail', function($query) use($search){
                        $query->whereHas('item',function($query) use($search){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                    })
                    ->orWhereHas('user',function($query) use($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
                })
                ->where(function($query){
                    $query->where(function($query){
                        $query->whereIn('place_from',$this->dataplaces)
                            ->whereIn('warehouse_from',$this->datawarehouses);
                    })->orWhere(function($query){
                        $query->whereIn('place_to',$this->dataplaces)
                            ->whereIn('warehouse_to',$this->datawarehouses);
                    });
                })
                ->whereIn('status',['2','3'])
                ->whereDoesntHave('used')
                ->get();

        foreach($data as $d) {
            if(!$d->inventoryTransferIn()->exists()){
                $details = [];

                foreach($d->inventoryTransferOutDetail as $row){
                    $details[] = [
                        'name'      => $row->item->name,
                        'origin'    => $row->itemStock->place->name.' - '.$row->itemStock->warehouse->name,
                        'qty'       => number_format($row->qty,3,',','.'),
                        'unit'      => $row->item->uomUnit->code,
                        'note'      => $row->note,
                    ];
                }
    
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - '.$d->user->name,
                    'details'           => $details,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function itemStock(Request $request)
    {
        $response   = [];
        $search     = $request->search;
        $data = ItemStock::where(function($query) use($search){
                        $query->whereHas('item',function($query) use($search){
                            $query->where('code', 'like', "%$search%")
                                ->orWhere('name','like',"%$search%");
                        });
                    })
                    ->whereIn('place_id',$this->dataplaces)
                    ->whereIn('warehouse_id',$this->datawarehouses)
                    ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->place->code.' - '.$d->warehouse->code.' Item '.$d->item->name.' Qty. '.number_format($d->qty,3,',','.').' '.$d->item->uomUnit->code,
                'qty'               => $d->qty,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function purchaseOrderDetail(Request $request){
        $response   = [];
        $search     = $request->search;

        $data = PurchaseOrderDetail::where(function($query) use($search,$request){
                    $query->where('item_id',$request->item_id)
                    ->whereHas('purchaseOrder',function($query) use($request){
                        $query->where('account_id',$request->account_id);  
                    });
                })
                ->whereIn('place_id',$this->dataplaces)
                ->whereIn('warehouse_id',$this->datawarehouses)
                ->get();

        foreach($data as $d) {
            if($d->getBalanceReceipt() > 0){
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->purchaseOrder->supplier->name.' - '.$d->purchaseOrder->code.' - '.$d->place->code.' - '.$d->warehouse->code.' Qty. '.number_format($d->getBalanceReceipt(),3,',','.').' '.$d->item->uomUnit->code,
                    'qty'               => number_format($d->getBalanceReceipt(),3,',','.'),
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function goodScaleItem(Request $request){
        $response   = [];

        $data = GoodScaleDetail::where(function($query) use($request){
                    $query->where(function($query)use($request){
                        $query->whereHas('item',function($query) use($request){
                            $query->where('code', 'like', "%$request->search%")
                                ->orWhere('name','like',"%$request->search%");
                        })
                        ->where('item_id',$request->item)
                        ->where('place_id',$request->place)
                        ->where('warehouse_id',$request->warehouse);
                    })
                    ->orWhereHas('goodScale',function($query)use($request){
                        $query->where('code','like',"%$request->search%");
                    });
                })
                ->whereHas('goodScale',function($query){
                    $query->whereIn('status',['2','3']);
                })
                ->whereDoesntHave('goodReceiptDetail')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->goodScale->code.' '.$d->item->name.' '.$d->qty_balance.' '.$d->item->uomUnit->code,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function shift(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Shift::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name .'|'. $d->time_in.'-'.$d->time_out,
                'data'          => $d
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function period(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = AttendancePeriod::where('name', 'like', "%$search%")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function place(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Place::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('name','like',"%$search%");
        })
        ->whereIn('id',$this->dataplaces)
        ->where('status','1')->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrder(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrder::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->hasBalanceMod()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDelivery(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrderDelivery::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhere('note_internal','like',"%$search%")
                ->orWhere('note_external','like',"%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                })
                ->orWhereHas('account',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])
        ->whereNotNull('send_status')
        ->get();

        foreach($data as $d) {
            if(!$d->marketingOrderDeliveryProcess()->exists()){
                $response[] = [
                    'id'   			=> $d->id,
                    'text' 			=> $d->code,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDeliveryProcess(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDeliveryProcess::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note_internal','like',"%$search%")
                    ->orWhere('note_external','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->whereHas('marketingOrderDelivery',function($query) use($account_id){
                        $query->whereHas('marketingOrder',function($query) use($account_id){
                            $query->where('account_id',$account_id);
                        });
                    });
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            if($d->balanceInvoice() > 0){
                $totalAll = 0;
                $taxAll = 0;
                $grandtotalAll = 0;

                $arrDetail = [];

                foreach($d->marketingOrderDelivery->marketingOrderDeliveryDetail as $row){
                    $price = $row->marketingOrderDetail->realPriceAfterGlobalDiscount();
                    $total = $price * $row->getBalanceQtySentMinusReturn();
                    if($row->marketingOrderDetail->tax_id > 0){
                        if($row->marketingOrderDetail->is_include_tax == '1'){
                            $total = $total / (1 + ($row->marketingOrderDetail->percent_tax / 100));
                        }
                    }
                    $tax = $total * ($row->marketingOrderDetail->percent_tax / 100);
                    $grandtotal = $total + $tax;
                    $arrDetail[] = [
                        'id'                => $row->id,
                        'item_id'           => $row->item_id,
                        'item_name'         => $row->item->name.' - '.$row->itemStock->place->code.' - '.$row->itemStock->warehouse->code,
                        'item_warehouse'    => $row->item->warehouseList(),
                        'unit'              => $row->item->sellUnit->code,
                        'code'              => $d->code,
                        'qty_sent'          => number_format($row->getBalanceQtySentMinusReturn(),3,',','.'),
                        'place_id'          => $row->place_id,
                        'warehouse_id'      => $row->warehouse_id,
                        'tax_id'            => $row->marketingOrderDetail->tax_id,
                        'is_include_tax'    => $row->marketingOrderDetail->is_include_tax,
                        'percent_tax'       => number_format($row->marketingOrderDetail->percent_tax,2,',','.'),
                        'total'             => number_format($total,2,',','.'),
                        'tax'               => number_format($tax,2,',','.'),
                        'grandtotal'        => number_format($grandtotal,2,',','.'),
                        'lookable_type'     => $row->getTable(),
                        'lookable_id'       => $row->id,
                        'qty_do'            => number_format($row->qty,3,',','.'),
                        'qty_return'        => number_format($row->qtyReturn(),3,',','.'),
                        'price'             => number_format($price,2,',','.'),
                        'note'              => $row->note,
                    ];
                    $totalAll += $total;
                    $taxAll += $tax;
                    $grandtotalAll += $grandtotal;
                }

                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Ven : '.$d->account->name. ' - Cust. '.$d->marketingOrderDelivery->marketingOrder->account->name,
                    'code'              => $d->code,
                    'details'           => $arrDetail,
                    'total'             => $d->marketingOrderDelivery->marketingOrder->total,
                    'tax'               => $d->marketingOrderDelivery->marketingOrder->tax,
                    'rounding'          => $d->marketingOrderDelivery->marketingOrder->rounding,
                    'grandtotal'        => $d->marketingOrderDelivery->marketingOrder->grandtotal,
                    'real_total'        => number_format($totalAll,2,',','.'),
                    'real_tax'          => number_format($taxAll,2,',','.'),
                    'real_grandtotal'   => number_format($grandtotalAll,2,',','.'),
                    'type'              => $d->getTable(),
                    'due_date'          => $d->marketingOrderDelivery->marketingOrder->valid_date,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderDownPayment(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderDownPayment::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->where('account_id',$account_id);
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();

        foreach($data as $d) {
            $arrNominal = $d->arrBalanceInvoice();
            if($d->balanceInvoice() > 0){
                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Cust. '.$d->account->name,
                    'code'              => $d->code,
                    'type'              => $d->getTable(),
                    'is_include_tax'    => $d->is_include_tax,
                    'percent_tax'       => $d->percent_tax,
                    'tax_id'            => $d->tax_id ? $d->tax_id : '0',
                    'post_date'         => date('d/m/y',strtotime($d->post_date)),
                    'due_date'          => $d->due_date,
                    'subtotal'          => number_format($d->subtotal,2,',','.'),
                    'discount'          => number_format($d->discount,2,',','.'),
                    'total'             => number_format($d->total,2,',','.'),
                    'tax'               => number_format($d->tax,2,',','.'),
                    'grandtotal'        => number_format($d->grandtotal,2,',','.'),
                    'balance'           => number_format($d->balanceInvoice(),2,',','.'),
                    'balance_array'     => $arrNominal,
                    'note'              => $d->note,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderInvoice(Request $request)
    {
        $response = [];
        $search     = $request->search;
        $account_id = $request->account_id;
        $data = MarketingOrderInvoice::where(function($query) use($search,$account_id){
            $query->where(function($query) use ($search){
                $query->where('code', 'like', "%$search%")
                    ->orWhere('note','like',"%$search%")
                    ->orWhereHas('user',function($query) use ($search){
                        $query->where('name','like',"%$search%")
                            ->orWhere('employee_no','like',"%$search%");
                    });
            })
            ->where(function($query) use ($account_id){
                if($account_id){
                    $query->where('account_id',$account_id);
                }
            });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])->get();
        
        foreach($data as $d) {
            if($d->balancePaymentIncoming() > 0){
                $arrNominalMain = $d->arrBalanceMemo();
                $arrDetail = [];
                foreach($d->marketingOrderInvoiceDeliveryProcess as $row){
                    $arrNominal = $row->arrBalanceMemo();
                    $arrDetail[] = [
                        'id'                => $row->id,
                        'code'              => $d->code,
                        'type'              => $row->getTable(),
                        'is_include_tax'    => $row->is_include_tax,
                        'percent_tax'       => $row->percent_tax,
                        'tax_id'            => $row->tax_id ? $row->tax_id : '0',
                        'total'             => number_format($arrNominal['total'],2,',','.'),
                        'tax'               => number_format($arrNominal['tax'],2,',','.'),
                        'total_after_tax'   => number_format($arrNominal['total_after_tax'],2,',','.'),
                        'rounding'          => number_format($arrNominal['rounding'],2,',','.'),
                        'grandtotal'        => number_format($arrNominal['grandtotal'],2,',','.'),
                        'downpayment'       => number_format($arrNominal['downpayment'],2,',','.'),
                        'balance'           => number_format($arrNominal['balance'],2,',','.'),
                    ];
                }

                $response[] = [
                    'id'   			    => $d->id,
                    'text' 			    => $d->code.' - Cust. '.$d->account->name,
                    'code'              => $d->code,
                    'type'              => $d->getTable(),
                    'post_date'         => date('d/m/y',strtotime($d->post_date)),
                    'due_date'          => $d->due_date,
                    'total'             => number_format($arrNominalMain['total'],2,',','.'),
                    'tax'               => number_format($arrNominalMain['tax'],2,',','.'),
                    'total_after_tax'   => number_format($arrNominalMain['total_after_tax'],2,',','.'),
                    'rounding'          => number_format($arrNominalMain['rounding'],2,',','.'),
                    'grandtotal'        => number_format($arrNominalMain['grandtotal'],2,',','.'),
                    'downpayment'       => number_format($arrNominalMain['downpayment'],2,',','.'),
                    'balance'           => number_format($arrNominalMain['balance'],2,',','.'),
                    'note'              => $d->note,
                    'details'           => $arrDetail,
                ];
            }
        }

        return response()->json(['items' => $response]);
    }

    public function transportation(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Transportation::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function outlet(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Outlet::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                })
                ->where('status','1')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			    => $d->id,
                'text' 			    => $d->code.' - '.$d->name,
                'address'           => $d->address,
                'province_id'       => $d->province_id,
                'province_name'     => $d->province->name,
                'city_id'           => $d->city_id,
                'city_name'         => $d->city->name,
                'district_id'       => $d->district_id,
                'district_name'     => $d->district->name,
                'subdistrict_id'    => $d->subdistrict_id,
                'subdistrict_name'  => $d->subdistrict->name,
                'cities'            => $d->province->getCity(),
            ];
        }

        return response()->json(['items' => $response]);
    }

    public function marketingOrderPlan(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = MarketingOrderPlan::where(function($query) use($search){
            $query->where('code', 'like', "%$search%")
                ->orWhereHas('user',function($query) use ($search){
                    $query->where('name','like',"%$search%")
                        ->orWhere('employee_no','like',"%$search%");
                });
        })
        ->whereDoesntHave('used')
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->whereIn('status',['2','3'])
        ->get();

        foreach($data as $d) {
            $details = [];

            foreach($d->MarketingOrderPlanDetail as $row){
                $details[] = [
                    'mopd_id'       => $row->id,
                    'item_id'       => $row->item_id,
                    'item_name'     => $row->item->name,
                    'qty_in_sell'   => number_format($row->qty,3,',','.'),
                    'qty_in_uom'    => number_format($row->qty * $row->item->sell_unit,3,',','.'),
                    'qty_in_pallet' => number_format($row->qty / $row->item->pallet_unit,3,',','.'),
                    'unit_sell'     => $row->item->sellUnit->code,
                    'unit_uom'      => $row->item->uomUnit->code,
                    'unit_pallet'   => $row->item->palletUnit->code,
                    'request_date'  => date('d/m/y',strtotime($row->request_date)),
                    'note'          => $row->note,
                ];
            }
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' Periode '.date('d/m/y',strtotime($d->start_date)).' - '.date('d/m/y',strtotime($d->end_date)),
                'table'         => $d->getTable(),
                'details'       => $details,
                'code'          => $d->code,
            ];
        }

        return response()->json(['items' => $response]);
    }
}
