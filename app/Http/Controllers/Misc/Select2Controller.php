<?php

namespace App\Http\Controllers\Misc;

use App\Helpers\CustomHelper;
use App\Models\ApprovalStage;
use App\Models\CostDistribution;
use App\Models\FundRequest;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\Line;
use App\Models\Menu;
use App\Models\PaymentRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\Region;
use App\Models\Place;
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
    
    protected $dataplaces, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user->userWarehouseArray();
    }
    
    public function city(Request $request)
    {
        $response = [];
        $search   = $request->search;
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 5")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
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
        $data = Region::where('name', 'like', "%$search%")->whereRaw("CHAR_LENGTH(code) = 2")->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name
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
                'stock_list'        => $d->currentStock($this->dataplaces),
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
                    ->orWhere('name', 'like', "%$search%");
                /* })->whereDoesntHave('childSub') */
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNull('is_hidden')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
                'uom'           => '-',
                'code'          => CustomHelper::encrypt($d->code),
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
                    ->orWhere('name', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->where('code','like',"100.01.01%")
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name,
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
                    ->orWhere('name', 'like', "%$search%");
                })->where('status','1')->get();

        foreach($data as $d) {
            $pre_text = str_repeat(" - ", $d->level);
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $pre_text.$d->code.' - '.$d->name,
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
                'text' 			=> $d->name.' - '.$d->phone.' Pos. '.$d->position->name.' Dep. '.$d->department->name,
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
                ->whereNull('nominal')
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

    public function coaJournal(Request $request)
    {   
        $arrCompany = Place::whereIn('id',$this->dataplaces)->get()->pluck('company_id');
        $response = [];
        $search   = $request->search;
        $data = Coa::where(function($query) use($search){
                    $query->where('code', 'like', "%$search%")
                    ->orWhere('name', 'like', "%$search%");
                 })->where('level',5)
                ->where('status','1')
                ->whereIn('company_id',$arrCompany)
                ->whereNotNull('show_journal')
                ->get();

        foreach($data as $d) {
            $response[] = [
                'id'   			=> $d->id,
                'text' 			=> $d->code.' - '.$d->name.' - '.$d->company->name,
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
                    'machine_id'        => $row->machine_id ? $row->machine_id : '',
                    'machine_name'      => $row->machine_id ? $row->machine->name : '',
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
                'coa_name'          => $d->coa_id ? $d->coa->name : '',
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
}
