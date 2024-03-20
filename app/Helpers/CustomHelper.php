<?php 

namespace App\Helpers;
use App\Jobs\ResetCogs;
use App\Jobs\ResetStock;
use App\Models\ApprovalMatrix;
use App\Models\LockPeriod;
use App\Models\ApprovalStage;
use App\Models\ApprovalSource;
use App\Models\OvertimeRequest;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateMenu;
use App\Models\PrintCounter;
use App\Models\Asset;
use App\Models\Capitalization;
use App\Models\CloseBill;
use App\Models\ClosingJournal;
use App\Models\Coa;
use App\Models\Depreciation;
use App\Models\EmployeeLeaveQuotas;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeTransfer;
use App\Models\GoodIssue;
use App\Models\GoodIssueRequest;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceiptMain;
use App\Models\GoodReceive;
use App\Models\GoodReturnIssue;
use App\Models\GoodReturnPO;
use App\Models\IncomingPayment;
use App\Models\InventoryRevaluation;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\Item;
use App\Models\ItemGroupWarehouse;
use App\Models\LeaveRequest;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReturn;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\Place;
use App\Models\ProductionIssueReceive;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\Retirement;
use App\Models\ShiftRequest;
use App\Models\User;
use App\Models\Notification;
use App\Models\Menu;
use App\Models\MenuCoa;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\LandedCost;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\MaterialRequest;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseRequest;
use App\Models\UsedData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomHelper {

	public static function encrypt($string)
    {
		if($string == ''){
			$val = "";
		}else{
			$val = strrev(implode('-',str_split(str_replace('=','',base64_encode($string)),5)));
		}
		
		return $val;
	}

	public static function decrypt($string){
		$val = base64_decode(str_replace('-','',strrev($string)));
		return $val;
	}

	public static function updateEmployeeTransfer($transfer){
		DB::beginTransaction();
		if(in_array($transfer->status,['2','3'])){
			$query = User::find($transfer->account_id);
			$query->place_id         = $transfer->plant_id;
			$query->manager_id               = $transfer->manager_id;
			
			$query->position_id               = $transfer->position_id;
			$query->save();

			$query_check_employee_transfer = EmployeeTransfer::where('account_id',$query->id)->whereIn('status',['2','3'])->get();
			$date = Carbon::parse($transfer->post_date);
			$year_later = Carbon::parse($transfer->post_date)->addYear();
		
			if(count($query_check_employee_transfer) == 1){
				EmployeeLeaveQuotas::create([
					'user_id'			=> $query->id,
					'leave_type_id'		=> 1,
					'paid_leave_quotas'	=> 0,
					'start_date'		=> strval($date->format('Y-m-d')),
					'end_date'			=> strval($year_later->format('Y-m-d')),
				]);
			}
			DB::commit();
		}
	}

	public static function revertBackEmployeeTransfer($transfer){
		DB::beginTransaction();
		if(in_array($transfer->status,['4','5'])){
			$latestTransfer = EmployeeTransfer::whereIn('status', [3, 2])
						->where('account_id', $transfer->account_id)
						->latest('created_at')
						->first();
			
			$query = User::find($transfer->account_id);
			$query->place_id         	  = $latestTransfer->plant_id;
			$query->manager_id            = $latestTransfer->manager_id;
			
			$query->position_id           = $latestTransfer->position_id;
			$query->save();
			DB::commit();
		}
	}

	public static function sendCogs($lookable_type = null, $lookable_id = null, $company_id = null, $place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $total = null, $type = null, $date = null, $area_id = null, $shading = null){
		$old_data = ItemCogs::where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
		if($type == 'IN'){
			ItemCogs::create([
				'lookable_type'		=> $lookable_type,
				'lookable_id'		=> $lookable_id,
				'company_id'		=> $company_id,
				'place_id'			=> $place_id,
				'warehouse_id'		=> $warehouse_id,
				'area_id'			=> $area_id,
				'item_id'			=> $item_id,
				'item_shading_id'	=> $shading ? $shading : NULL,
				'qty_in'			=> $qty,
				'price_in'			=> $qty > 0 ? $total / $qty : 0,
				'total_in'			=> $total,
				'qty_final'			=> $old_data ? $old_data->qty_final + $qty : $qty,
				'price_final'		=> $old_data ? (($old_data->total_final + $total) / ($old_data->qty_final + $qty)) : ($qty > 0 ? $total / $qty : 0),
				'total_final'		=> $old_data ? round(($old_data->total_final + $total),2) : $total,
				'date'				=> $date,
				'type'				=> $type
			]);
		}elseif($type == 'OUT'){
			if($old_data){
				if($lookable_type == 'good_returns'){
					$priceout = round($total / $qty,3);
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $total;
					$pricefinal = $qtybalance > 0 ? round($totalfinal / $qtybalance,2) : 0;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> $priceout,
						'total_out'			=> $total,
						'qty_final'			=> $qtybalance,
						'price_final'		=> $pricefinal,
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}elseif($lookable_type == 'production_issue_receives'){
					$priceeach = $old_data->total_final / $old_data->qty_final;
					$totalout = round($priceeach * $qty,2);
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $totalout;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> round($priceeach,2),
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> round($priceeach,2),
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}elseif($lookable_type == 'purchase_memos'){
					$priceeach = $total / $qty;
					$totalout = $total;
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $total;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> round($priceeach,2),
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> round($priceeach,2),
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}else{
					$priceeach = $total / $qty;
					$totalout = $total;
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $totalout;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> $priceeach,
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> $priceeach,
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}
			}
		}

		/* ResetCogs::dispatch($date,$place_id,$item_id); */
	}

	public static function sendCogs2($lookable_type = null, $lookable_id = null, $company_id = null, $place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $total = null, $type = null, $date = null, $area_id = null, $shading = null){
		$old_data = ItemCogs::where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->whereDate('date','<=',$date)->orderByDesc('date')->orderByDesc('id')->first();
		if($type == 'IN'){
			ItemCogs::create([
				'lookable_type'		=> $lookable_type,
				'lookable_id'		=> $lookable_id,
				'company_id'		=> $company_id,
				'place_id'			=> $place_id,
				'warehouse_id'		=> $warehouse_id,
				'area_id'			=> $area_id,
				'item_id'			=> $item_id,
				'item_shading_id'	=> $shading ? $shading : NULL,
				'qty_in'			=> $qty,
				'price_in'			=> $qty > 0 ? round($total / $qty,2) : 0,
				'total_in'			=> $total,
				'qty_final'			=> $old_data ? $old_data->qty_final + $qty : $qty,
				'price_final'		=> $old_data ? round((($old_data->total_final + $total) / ($old_data->qty_final + $qty)),2) : ($qty > 0 ? round($total / $qty,2) : 0),
				'total_final'		=> $old_data ? round(($old_data->total_final + $total),2) : $total,
				'date'				=> $date,
				'type'				=> $type
			]);
		}elseif($type == 'OUT'){
			if($old_data){
				if($lookable_type == 'good_returns'){
					$priceout = round($total / $qty,3);
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $total;
					$pricefinal = $qtybalance > 0 ? round($totalfinal / $qtybalance,2) : 0;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> $priceout,
						'total_out'			=> $total,
						'qty_final'			=> $qtybalance,
						'price_final'		=> $pricefinal,
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}elseif($lookable_type == 'production_issue_receives'){
					$priceeach = $old_data->total_final / $old_data->qty_final;
					$totalout = round($priceeach * $qty,2);
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $totalout;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> round($priceeach,2),
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> round($priceeach,2),
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}elseif($lookable_type == 'purchase_memos'){
					$priceeach = $total / $qty;
					$totalout = $total;
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $total;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> round($priceeach,2),
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> round($priceeach,2),
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}else{
					$priceeach = $old_data->total_final / $old_data->qty_final;
					$totalout = round($priceeach * $qty,0);
					$qtybalance = $old_data->qty_final - $qty;
					$totalfinal = $old_data->total_final - $totalout;
					ItemCogs::create([
						'lookable_type'		=> $lookable_type,
						'lookable_id'		=> $lookable_id,
						'company_id'		=> $company_id,
						'place_id'			=> $place_id,
						'warehouse_id'		=> $warehouse_id,
						'area_id'			=> $area_id,
						'item_id'			=> $item_id,
						'item_shading_id'	=> $shading ? $shading : NULL,
						'qty_out'			=> $qty,
						'price_out'			=> $priceeach,
						'total_out'			=> $totalout,
						'qty_final'			=> $qtybalance,
						'price_final'		=> $priceeach,
						'total_final'		=> $totalfinal,
						'date'				=> $date,
						'type'				=> $type
					]);
				}
			}
		}
	}

	public static function sendStock($place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $type = null, $area_id = null, $shading = null){
		$old_data = ItemStock::where('place_id',$place_id)->where('item_id',$item_id)->where('warehouse_id',$warehouse_id)->where('area_id',$area_id)->where('item_shading_id',$shading)->first();
		if($old_data){
			$old_data->update([
				'qty' => $type == 'IN' ? $old_data->qty + $qty : $old_data->qty - $qty,
			]);
		}else{
			ItemStock::create([
				'place_id'			=> $place_id,
				'warehouse_id'		=> $warehouse_id,
				'area_id'			=> $area_id,
				'item_id'			=> $item_id,
				'item_shading_id'	=> $shading ? $shading : NULL,
				'qty'				=> $type == 'IN' ? $qty : 0 - $qty,
			]);
		}
	}
	
	public static function compare($value1,$sign,$value2){
		$passed = false;

		if($sign == '>'){
			if($value1 > $value2){
				$passed = true;
			}
		}elseif($sign == '>='){
			if($value1 >= $value2){
				$passed = true;
			}
		}elseif($sign == '='){
			if($value1 == $value2){
				$passed = true;
			}
		}elseif($sign == '<'){
			if($value1 < $value2){
				$passed = true;
			}
		}elseif($sign == '<='){
			if($value1 <= $value2){
				$passed = true;
			}
		}

		return $passed;
	}

	public static function compareRange($value1,$value2,$value3){
		$passed = false;

		if($value1 >= $value2 && $value1 <= $value3){
			$passed = true;
		}

		return $passed;
	}

	public static function sendApproval($table_name,$table_id,$note){
		$resetdata = ApprovalSource::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();

		foreach($resetdata as $rowreset){
			foreach($rowreset->approvalMatrix as $detailmatrix){
				$detailmatrix->delete();
			}
			$rowreset->delete();
		}

		$data = DB::table($table_name)->where('id',$table_id)->first();

		$approvalTemplate = ApprovalTemplate::where('status','1')
		->whereHas('approvalTemplateMenu',function($query) use($table_name){
			$query->where('table_name',$table_name);
		})
		->whereHas('approvalTemplateOriginator',function($query){
			$query->where('user_id',session('bo_id'));
		})->get();
		
		$count = 0;

		$currency_rate = isset($data->currency_rate) ? $data->currency_rate : 1;

		foreach($approvalTemplate as $row){
			
			$source = ApprovalSource::create([
				'code'			=> strtoupper(uniqid()),
				'user_id'		=> session('bo_id'),
				'date_request'	=> date('Y-m-d H:i:s'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'note'			=> $note,
			]);

			$passed = true;

			$isGroupItem = false;

			if($row->approvalTemplateItemGroup()->exists()){
				$isGroupItem = true;
			}

			#if check nominal
			if($row->is_check_nominal){
				if($row->sign !== '~'){
					if($isGroupItem){
						#groupitem, checknominal dan bukanrange
						$arrGroupItem = [];
						$passedGroupItem = false;

						foreach($row->approvalTemplateItemGroup as $rowgroupitem){
							$arrGroupItem[] = $rowgroupitem->item_group_id;
						}

						foreach($source->lookable->details as $rowdetail){
							if($rowdetail->item()->exists()){
								$topGroupId = $rowdetail->item->itemGroup->getTopParent($rowdetail->item->itemGroup);
								if(in_array($topGroupId,$arrGroupItem)){
									$passedGroupItem = true;
								}
							}
						}

						if($passedGroupItem){
							if(!self::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}elseif($row->is_coa_detail){
						$passedGroupCoa = false;
						foreach($source->lookable->details as $rowdetail){
							if($rowdetail->coa()->exists()){
								$passedGroupCoa = true;
							}
						}
						if($passedGroupCoa){
							if(!self::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}else{
						#checknominal dan bukanrange
						if(!self::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
							$passed = false;
						}
					}					
				}else{
					if($isGroupItem){
						#groupitem, checknominal dan range
						$arrGroupItem = [];
						$passedGroupItem = false;

						foreach($row->approvalTemplateItemGroup as $rowgroupitem){
							$arrGroupItem[] = $rowgroupitem->item_group_id;
						}

						foreach($source->lookable->details as $rowdetail){
							if($rowdetail->item()->exists()){
								$topGroupId = $rowdetail->item->itemGroup->getTopParent($rowdetail->item->itemGroup);
								if(in_array($topGroupId,$arrGroupItem)){
									$passedGroupItem = true;
								}
							}
						}

						if($passedGroupItem){
							if(!self::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}elseif($row->is_coa_detail){
						$passedGroupCoa = false;
						foreach($source->lookable->details as $rowdetail){
							if($rowdetail->coa()->exists()){
								$passedGroupCoa = true;
							}
						}
						if($passedGroupCoa){
							if(!self::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}else{
						#checknominal dan range
						if(!self::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
							$passed = false;
						}
					}
				}
			}

			#if check benchmark
			if($row->is_check_benchmark){
				if($isGroupItem){
					#groupitem, checknominal dan bukanrange
					$arrGroupItem = [];
					$passedGroupItem = false;

					foreach($row->approvalTemplateItemGroup as $rowgroupitem){
						$arrGroupItem[] = $rowgroupitem->item_group_id;
					}

					foreach($source->lookable->details as $rowdetail){
						if($rowdetail->item()->exists()){
							$topGroupId = $rowdetail->item->itemGroup->getTopParent($rowdetail->item->itemGroup);
							if(in_array($topGroupId,$arrGroupItem)){
								$passedGroupItem = true;
							}
						}
					}

					if($passedGroupItem){
						$totalDoc = 0;
						$totalBench = 0;
						$percentDiff = 0;
						foreach($source->lookable->details as $rowdetail){
							$priceDoc = round(($rowdetail->priceAfterDiscount() * $currency_rate) / $rowdetail->qty_conversion,2);
							$priceBench = $rowdetail->item->lastBenchmarkPricePlant($rowdetail->place_id);
							$totalDoc += $priceDoc * $rowdetail->qty_conversion * $rowdetail->qty;
							$totalBench += $priceBench * $rowdetail->qty_conversion * $rowdetail->qty;
						}
						$percentDiff = $totalBench > 0 ? ((($totalDoc - $totalBench) / $totalBench) * 100) : 0;
						if($row->sign !== '~'){
							if(!self::compare($percentDiff,$row->sign,$row->nominal)){
								$passed = false;
							}
						}else{
							if(!self::compareRange($percentDiff,$row->nominal,$row->nominal_final)){
								$passed = false;
							}
						}
					}else{
						$passed = false;
					}
				}
			}

			#if group item saja tanpa check nominal dan check benchmark
			if(!$row->is_check_nominal && !$row->is_check_benchmark &&$isGroupItem){
				$arrGroupItem = [];
				$passedGroupItem = false;
				foreach($row->approvalTemplateItemGroup as $rowgroupitem){
					$arrGroupItem[] = $rowgroupitem->item_group_id;
				}

				foreach($source->lookable->details as $rowdetail){
					if($rowdetail->item()->exists()){
						$topGroupId = $rowdetail->item->itemGroup->getTopParent($rowdetail->item->itemGroup);
						if(in_array($topGroupId,$arrGroupItem)){
							$passedGroupItem = true;
						}
					}
				}

				if(!$passedGroupItem){
					$passed = false;
				}

				if($row->is_coa_detail){
					$passedGroupCoa = false;
					foreach($source->lookable->details as $rowdetail){
						if($rowdetail->coa()->exists()){
							$passedGroupCoa = true;
						}
					}
					if($passedGroupCoa){
						$passed = true;
					}else{
						$passed = false;
					}
				}
			}

			if($passed == true){
				
				$count = 0;

				foreach($row->approvalTemplateStage()->orderBy('id')->get() as $rowTemplateStage){
					$status = $count == 0 ? '1': '0';
					foreach($rowTemplateStage->approvalStage->approvalStageDetail as $rowStageDetail){
						ApprovalMatrix::create([
							'code'							=> strtoupper(Str::random(30)),
							'approval_template_stage_id'	=> $rowTemplateStage->id,
							'approval_source_id'			=> $source->id,
							'user_id'						=> $rowStageDetail->user_id,
							'date_request'					=> date('Y-m-d H:i:s'),
							'status'						=> $status
						]);
					}
					$count++;
					
				}
				
			}
		}
		
		if($count == 0){
			DB::table($table_name)->where('id',$table_id)->update([
				'status'	=> '2'
			]);

			#lek misal g ada approval
			if($table_name == 'material_requests'){
				$mr = MaterialRequest::find($table_id);
				$mr->materialRequestDetail()->update([
					'status'	=> '1'
				]);
			}

			if($table_name == 'good_issue_requests'){
				$mr = GoodIssueRequest::find($table_id);
				$mr->goodIssueRequestDetail()->update([
					'status'	=> '1'
				]);
			}

			if(isset($data->account_id)){
				self::sendJournal($table_name,$table_id,$data->account_id);
			}else{
				self::sendJournal($table_name,$table_id,null);
			}
		}
	}

	public static function sendNotification($table_name = null, $table_id = null, $title = null, $note = null, $to = null){
		
		$menu = Menu::where('table_name',$table_name)->first();

		$arrUser = [];

		if($menu){
			foreach($menu->menuUser as $row){
				$arrUser[] = $row->user_id;
			}

			$arrUser = array_values(array_unique($arrUser));

			$targets = User::whereIn('id',$arrUser)->where('status','1')->where('type','1')->get();

			$adato = false;

			foreach($targets as $row){
				if($to){
					if($row->id == $to){
						$adato = true;
					}
				}
				Notification::create([
					'code'				=> Str::random(20),
					'menu_id'			=> $menu->id,
					'from_user_id'		=> session('bo_id'),
					'to_user_id'		=> $row->id,
					'lookable_type'		=> $table_name,
					'lookable_id'		=> $table_id,
					'title'				=> $title,
					'note'				=> $note,
					'status'			=> '1'
				]);
			}
			
			if($to){
				if($adato == false){
					Notification::create([
						'code'				=> Str::random(20),
						'menu_id'			=> $menu->id,
						'from_user_id'		=> session('bo_id'),
						'to_user_id'		=> $to,
						'lookable_type'		=> $table_name,
						'lookable_id'		=> $table_id,
						'title'				=> $title,
						'note'				=> $note,
						'status'			=> '1'
					]);
				}
			}
		}
	}

	public static function sendNotificationWithFrom($table_name = null, $table_id = null, $title = null, $note = null, $from = null, $to = null){
		
		$menu = Menu::where('table_name',$table_name)->first();

		if($menu){
			Notification::create([
				'code'				=> Str::random(20),
				'menu_id'			=> $menu->id,
				'from_user_id'		=> $from,
				'to_user_id'		=> $to,
				'lookable_type'		=> $table_name,
				'lookable_id'		=> $table_id,
				'title'				=> $title,
				'note'				=> $note,
				'status'			=> '1'
			]);
		}
	}

	public static function sendCogsFromReset($table_name = null,$table_id = null){
		if($table_name == 'good_receipts'){

			$gr = GoodReceipt::find($table_id);

			$gr->journal->journalDetail()->delete();

			$coa_credit = Coa::where('code','200.01.03.01.02')->where('company_id',$gr->company_id)->first();

			foreach($gr->goodReceiptDetail as $rowdetail){

				$rowtotal = $rowdetail->getRowTotal() * $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate;
				
				JournalDetail::create([
					'journal_id'	=> $gr->journal->id,
					'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
					'place_id'		=> $rowdetail->place_id,
					'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
					'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
					'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
					'warehouse_id'	=> $rowdetail->warehouse_id,
					'project_id'	=> $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : NULL,
					'type'			=> '1',
					'nominal'		=> $rowtotal,
					'nominal_fc'	=> $rowdetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? $rowtotal : $rowdetail->getRowTotal(),
					'note'			=> $gr->delivery_no,
				]);

				if($coa_credit){
					JournalDetail::create([
						'journal_id'	=> $gr->journal->id,
						'coa_id'		=> $coa_credit->id,
						'place_id'		=> $rowdetail->place_id,
						'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
						'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
						'account_id'	=> $coa_credit->bp_journal ? $gr->account_id : NULL,
						'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
						'warehouse_id'	=> $rowdetail->warehouse_id,
						'project_id'	=> $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $rowtotal,
						'nominal_fc'	=> $rowdetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? $rowtotal : $rowdetail->getRowTotal(),
					]);
				}
				
				self::sendCogs2('good_receipts',
					$gr->id,
					$gr->company_id,
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					$rowtotal,
					'IN',
					$gr->post_date,
					NULL,
					NULL,
				);
			}
			
		}elseif($table_name == 'good_receives'){

			$gr = GoodReceive::find($table_id);

			foreach($gr->goodReceiveDetail as $row){
				self::sendCogs2('good_receives',
					$gr->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total * $gr->currency_rate,
					'IN',
					$gr->post_date,
					$row->area_id,
					$row->item_shading_id ? $row->item_shading_id : NULL,
				);
			}
		}elseif($table_name == 'good_issues'){

			$gr = GoodIssue::find($table_id);

			foreach($gr->goodIssueDetail as $row){
				$total = round($row->itemStock->priceDate($gr->post_date),2) * $row->qty;

				self::sendCogs2('good_issues',
					$gr->id,
					$row->itemStock->place->company_id,
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->itemStock->item_id,
					$row->qty,
					$total,
					'OUT',
					$gr->post_date,
					$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
					$row->itemStock->item_shading_id ? $row->itemStock->item_shading_id : NULL,
				);

				$row->update([
					'total'	=> $total,
				]);
			}

		}elseif($table_name == 'good_returns'){

			$gr = GoodReturnPO::find($table_id);

			foreach($gr->goodReturnPODetail as $row){
				$rowtotal = $row->getRowTotal() * $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_rate;

				self::sendCogs2('good_returns',
					$gr->id,
					$row->goodReceiptDetail->place->company_id,
					$row->goodReceiptDetail->place_id,
					$row->goodReceiptDetail->warehouse_id,
					$row->item_id,
					$row->qtyConvert(),
					$rowtotal,
					'OUT',
					$gr->post_date,
					NULL,
					NULL,
				);
			}

		}elseif($table_name == 'good_return_issues'){

			$gr = GoodReturnIssue::find($table_id);

			foreach($gr->goodReturnIssueDetail as $row){
				self::sendCogs2($gr->getTable(),
					$gr->id,
					$row->goodIssueDetail->itemStock->place->company_id,
					$row->goodIssueDetail->itemStock->place_id,
					$row->goodIssueDetail->itemStock->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total,
					'IN',
					$gr->post_date,
					$row->goodIssueDetail->itemStock->area_id ? $row->goodIssueDetail->itemStock->area_id : NULL,
					$row->goodIssueDetail->itemStock->item_shading_id ? $row->goodIssueDetail->itemStock->item_shading_id : NULL,
				);
			}

		}elseif($table_name == 'marketing_order_returns'){

		}elseif($table_name == 'landed_costs'){
			
			$lc = LandedCost::find($table_id);

			foreach($lc->landedCostDetail as $rowdetail){
				self::sendCogs2('landed_costs',
					$lc->id,
					$rowdetail->place->company_id,
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					0,
					$rowdetail->nominal * $lc->currency_rate,
					'IN',
					$lc->post_date,
					NULL,
					NULL,
				);
			}

		}elseif($table_name == 'inventory_revaluations'){

		}elseif($table_name == 'inventory_transfer_outs'){

		}elseif($table_name == 'inventory_transfer_ins'){

		}elseif($table_name == 'purchase_memos'){
			$pm = PurchaseMemo::find($table_id);

			foreach($pm->purchaseMemoDetail as $row){
				if($row->lookable_type == 'purchase_invoice_details'){
					if($row->lookable->lookable_type == 'good_receipt_details'){
						$total = $row->total * $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;

						self::sendCogs2('purchase_memos',
							$pm->id,
							$pm->company_id,
							$row->lookable->lookable->place_id,
							$row->lookable->lookable->warehouse_id,
							$row->lookable->lookable->item_id,
							round($row->qty * $row->lookable->lookable->qty_conversion,3),
							$total,
							'OUT',
							$pm->post_date,
							NULL,
							NULL,
						);
					}
				}
			}
		}
	}

	public static function sendJournal($table_name = null,$table_id = null,$account_id = null){

		$data = DB::table($table_name)->where('id',$table_id)->first();

		if($table_name == 'good_receipts'){

			$gr = GoodReceipt::find($table_id);

			$arrdata = json_decode(json_encode($gr), true);

			$arrNote = [];

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $gr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'good_receipts',
				'lookable_id'	=> $gr->id,
				'post_date'		=> $data->post_date,
				'note'			=> 'GOODS RECEIPT PO - '.$gr->account->employee_no,
				'status'		=> '3'
			]);

			$coa_credit = Coa::where('code','200.01.03.01.02')->where('company_id',$gr->company_id)->first();

			foreach($gr->goodReceiptDetail as $rowdetail){

				$rowtotal = $rowdetail->total * $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate;

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
					'place_id'		=> $rowdetail->place_id,
					'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
					'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
					'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
					'warehouse_id'	=> $rowdetail->warehouse_id,
					'project_id'	=> $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : NULL,
					'type'			=> '1',
					'nominal'		=> floatval($rowtotal),
					'nominal_fc'	=> $rowdetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? floatval($rowtotal) : floatval($rowdetail->getRowTotal()),
					'note'			=> $gr->delivery_no,
				]);

				if($coa_credit){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coa_credit->id,
						'place_id'		=> $rowdetail->place_id,
						'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
						'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
						'account_id'	=> $coa_credit->bp_journal ? $gr->account_id : NULL,
						'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
						'warehouse_id'	=> $rowdetail->warehouse_id,
						'project_id'	=> $rowdetail->purchaseOrderDetail->project_id ? $rowdetail->purchaseOrderDetail->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> floatval($rowtotal),
						'nominal_fc'	=> $rowdetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? floatval($rowtotal) : floatval($rowdetail->getRowTotal()),
					]);
				}

				self::sendCogs('good_receipts',
					$gr->id,
					$gr->company_id,
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					floatval($rowtotal),
					'IN',
					$gr->post_date,
					NULL,
					NULL,
				);

				self::sendStock(
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					'IN',
					NULL,
					NULL,
				);
			}

			$gr->updateRootDocumentStatusDone();
		}elseif($table_name == 'shift_requests'){
			$sr = ShiftRequest::find($table_id);
			
			foreach ($sr->shiftRequestDetail as $key => $row) {
				
				$query = EmployeeSchedule::create([
					'user_id' 	=> $sr->account_id,
					'shift_id' 	=> $row->shift_id, 
					'date' 		=> $row->date,
					'status'	=> '1',
					'shift_request_id'=> $sr->id,
				]);
				
				$query_schedule_update=EmployeeSchedule::find($row->employee_schedule_id);
				$query_schedule_update->status = '2';

				$query_schedule_update->save();
			}
			
		}elseif($table_name == 'material_requests'){

		}elseif($table_name == 'good_issue_requests'){

		}elseif($table_name == 'leave_requests'){
			$lr = LeaveRequest::find($table_id);
			$user= $lr->account;
			$schedule = [];
			
			// Convert the start date to a Carbon instance
			$currentDate = Carbon::parse($lr->start_date);
			
			if($lr->leaveType->furlough_type == 7 ){
				while (count($schedule) < 90) {
					$parse_date = Carbon::parse($currentDate->format('Y-m-d'))->toDateString();
					$query_schedule_in_date = EmployeeSchedule::where('date',$parse_date)
					->where('user_id',$user->employee_no)
					->delete();
					// Check if the current day is not a Sunday (dayOfWeek 0)
					if ($currentDate->dayOfWeek != 0) {
					
						$schedule[] = $currentDate->toDateString();
						$query = EmployeeSchedule::create([
							'user_id' 	=> $user->employee_no,
							'shift_id' 	=> 4, 
							'date' 		=> $currentDate,
							'status'	=> '4',
						]);
					}
		
					// Move to the next day
					$currentDate->addDay();
				}
			}	
			
			
		}elseif($table_name == 'retirements'){
			$ret = Retirement::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $ret->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'retirements',
				'lookable_id'	=> $ret->id,
				'currency_id'	=> $ret->currency_id,
				'currency_rate'	=> $ret->currency_rate,
				'post_date'		=> $ret->post_date,
				'note'			=> $ret->code,
				'status'		=> '3'
			]);

			foreach($ret->retirementDetail as $row){
				$totalDepre = $row->asset->nominal - $row->asset->book_balance;

				if($totalDepre > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->asset->assetGroup->depreciation_coa_id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> floatval($totalDepre * $ret->currency_rate),
						'nominal_fc'	=> $ret->currency->type == '1' ? floatval($totalDepre * $ret->currency_rate) : floatval($totalDepre),
					]);
				}

				if($row->asset->book_balance > 0 && $row->retirement_nominal == 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> Coa::where('code','701.01.01.01.07')->where('company_id',$ret->company_id)->first()->id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> floatval($row->asset->book_balance * $ret->currency_rate),
						'nominal_fc'	=> $ret->currency->type == '1' ? floatval($row->asset->book_balance * $ret->currency_rate) : floatval($row->asset->book_balance),
					]);
				}

				if($row->retirement_nominal > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> Coa::where('code','100.01.01.99.05')->where('company_id',$ret->company_id)->first()->id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> floatval($row->retirement_nominal * $ret->currency_rate),
						'nominal_fc'	=> $ret->currency->type == '1' ? floatval($row->retirement_nominal * $ret->currency_rate) : floatval($row->retirement_nominal),
					]);

					$balanceProfitLoss = ($totalDepre + $row->retirement_nominal) - $row->asset->nominal;
					$coaProfitLoss = $row->coa_id;
					if($balanceProfitLoss > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaProfitLoss,
							'place_id'		=> $row->asset->place_id,
							'type'			=> '2',
							'nominal'		=> floatval($balanceProfitLoss * $ret->currency_rate),
							'nominal_fc'	=> $ret->currency->type == '1' ? floatval($balanceProfitLoss * $ret->currency_rate) : floatval($balanceProfitLoss),
						]);
					}

					if($balanceProfitLoss < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaProfitLoss,
							'place_id'		=> $row->asset->place_id,
							'type'			=> '1',
							'nominal'		=> floatval(abs($balanceProfitLoss) * $ret->currency_rate),
							'nominal_fc'	=> $ret->currency->type == '1' ? floatval(abs($balanceProfitLoss) * $ret->currency_rate) : floatval(abs($balanceProfitLoss)),
						]);
					}
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '2',
					'nominal'		=> floatval($row->asset->nominal * $ret->currency_rate),
					'nominal_fc'	=> $ret->currency->type == '1' ? floatval($row->asset->nominal * $ret->currency_rate) : floatval($row->asset->nominal),
				]);

				self::updateBalanceAsset($row->asset_id,floatval($row->asset->book_balance),'OUT',$table_name);
			}
		
		}elseif($table_name == 'incoming_payments'){

			$ip = IncomingPayment::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $ip->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'incoming_payments',
				'lookable_id'	=> $ip->id,
				'currency_id'	=> $ip->currency_id,
				'currency_rate'	=> $ip->currency_rate,
				'post_date'		=> $ip->post_date,
				'note'			=> $ip->note,
				'status'		=> '3'
			]);

			$arrNote = [];
			
			if($ip){
				if($ip->wtax > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $ip->wTaxMaster->coa_purchase_id,
						'account_id'	=> $ip->wTaxMaster->coaPurchase->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
						'type'			=> '1',
						'nominal'		=> floatval($ip->wtax * $ip->currency_rate),
						'nominal_fc'	=> $ip->currency->type == '1' ? $ip->wtax * $ip->currency_rate : $ip->wtax,
					]);
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $ip->coa_id,
					'account_id'	=> $ip->coa->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
					'type'			=> '1',
					'nominal'		=> floatval($ip->grandtotal * $ip->currency_rate),
					'nominal_fc'	=> $ip->currency->type == '1' ? floatval($ip->grandtotal * $ip->currency_rate) : floatval($ip->grandtotal),
				]);

				$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$ip->company_id)->first();
				$coareceivable = Coa::where('code','100.01.03.03.02')->where('company_id',$ip->company_id)->first();
				$coapiutangusaha = Coa::where('code','100.01.03.01.01')->where('company_id',$ip->company_id)->first();

				foreach($ip->incomingPaymentDetail as $row){
					if($row->lookable_type == 'coas'){
						if($row->cost_distribution_id){
							$total = $row->total;
							$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
							$accumulation = 0;
							foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
								if($key == $lastIndex){
									$nominal = $total - $accumulation;
								}else{
									$nominal = round(($rowcost->percentage / 100) * $total);
									$accumulation += $nominal;
								}
								JournalDetail::create([
									'journal_id'                    => $query->id,
									'cost_distribution_detail_id'   => $rowcost->id,
									'coa_id'                        => $row->lookable_id,
									'place_id'                      => $rowcost->place_id ? $rowcost->place_id : NULL,
									'line_id'                       => $rowcost->line_id ? $rowcost->line_id : NULL,
									'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
									'account_id'                    => $row->lookable->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
									'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
									'warehouse_id'                  => $rowcost->warehouse_id ? $rowcost->warehouse_id : NULL,
									'type'                          => '2',
									'nominal'                       => floatval($nominal * $ip->currency_rate),
									'nominal_fc'					=> $ip->currency->type == '1' ? floatval($nominal * $ip->currency_rate) : floatval($nominal),
									'note'							=> $row->note,
								]);
							}
						}else{
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $row->lookable_id,
								'account_id'	=> $ip->account_id ? $ip->account_id : NULL,
								'type'			=> '2',
								'nominal'		=> floatval($row->total * $ip->currency_rate),
								'nominal_fc'	=> $ip->currency->type == '1' ? floatval($row->total * $ip->currency_rate) : floatval($row->total),
								'note'			=> $row->note,
							]);
						}
						
					}elseif($row->lookable_type == 'outgoing_payments'){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coareceivable->id,
							'account_id'	=> $coareceivable->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
							'type'			=> '2',
							'nominal'		=> floatval($row->total * $ip->currency_rate),
							'nominal_fc'	=> $ip->currency->type == '1' ? floatval($row->total * $ip->currency_rate) : floatval($row->total),
							'note'			=> $row->note,
						]);
						CustomHelper::removeCountLimitCredit($row->lookable->account_id,floatval($row->total * $ip->currency_rate));
						if(self::checkArrayRaw($arrNote,$row->lookable->code) < 0){
							$arrNote[] = $row->lookable->code;
						}
					}elseif($row->lookable_type == 'marketing_order_invoices' || $row->lookable_type == 'marketing_order_down_payments' || $row->lookable_type == 'marketing_order_memos'){
						if($row->lookable_type == 'marketing_order_memos'){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coapiutangusaha->id,
								'account_id'	=> $coapiutangusaha->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
								'type'			=> '1',
								'nominal'		=> floatval(abs($row->total * $ip->currency_rate)),
								'nominal_fc'	=> $ip->currency->type == '1' ? floatval(abs($row->total * $ip->currency_rate)) : floatval(abs($row->total)),
								'note'			=> $row->note,
							]);
						}else{
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coapiutangusaha->id,
								'account_id'	=> $coapiutangusaha->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
								'type'			=> '2',
								'nominal'		=> floatval($row->total * $ip->currency_rate),
								'nominal_fc'	=> $ip->currency->type == '1' ? floatval($row->total * $ip->currency_rate) : floatval($row->total),
								'note'			=> $row->note,
							]);
							if($row->lookable_type == 'marketing_order_down_payments'){
								self::addDeposit($row->lookable->account_id,floatval($row->total * $ip->currency_rate));
							}
						}
						CustomHelper::removeCountLimitCredit($row->lookable->account_id,floatval($row->total * $ip->currency_rate));
						if(self::checkArrayRaw($arrNote,$row->lookable->code) < 0){
							$arrNote[] = $row->lookable->code;
						}
					}else{
						
					}

					if($row->rounding > 0 || $row->rounding < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coarounding->id,
							'account_id'	=> $coarounding->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
							'type'			=> $row->rounding > 0 ? '2' : '1',
							'nominal'		=> floatval(abs($row->rounding * $ip->currency_rate)),
							'nominal_fc'	=> $ip->currency->type == '1' ? floatval(abs($row->rounding * $ip->currency_rate)) : floatval(abs($row->rounding)),
						]);
					}
				}
			}

		}elseif($table_name == 'payment_requests'){
			
			$pr = PaymentRequest::find($table_id);
			
			if($pr->paymentRequestCross()->exists() && $pr->balance == 0){
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $pr->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> 'payment_requests',
					'lookable_id'	=> $pr->id,
					'currency_id'	=> $pr->currency_id,
					'currency_rate'	=> $pr->currency_rate,
					'post_date'		=> $pr->post_date,
					'note'			=> $pr->note,
					'status'		=> '3'
				]);
	
				foreach($pr->paymentRequestDetail as $row){
					if($row->cost_distribution_id){
						$total = $row->nominal;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : NULL,
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : NULL,
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
								'account_id'                    => $row->coa->bp_journal ? $pr->account_id : NULL,
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
								'warehouse_id'                  => $rowcost->warehouse_id ? $rowcost->warehouse_id : NULL,
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '1',
								'nominal'                       => floatval($nominal * $pr->currency_rate),
								'nominal_fc'					=> $pr->currency->type == '1' ? floatval($nominal * $pr->currency_rate) : floatval($nominal),
							]);
						}
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'account_id'	=> $row->coa->bp_journal ? $pr->account_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'warehouse_id'	=> $row->warehouse_id ? $row->warehouse_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> floatval($row->nominal * $pr->currency_rate),
							'nominal_fc'	=> $pr->currency->type == '1' ? floatval($row->nominal * $pr->currency_rate) : floatval($row->nominal),
						]);
					}
				}

				if($pr->rounding){
					$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$pr->company_id)->first();
					#start journal rounding
					if($pr->rounding > 0 || $pr->rounding < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coarounding->id,
							'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
							'type'			=> $pr->rounding > 0 ? '1' : '2',
							'nominal'		=> floatval(abs($pr->rounding * $pr->currency_rate)),
							'nominal_fc'	=> $pr->currency->type == '1' ? floatval(abs($pr->rounding * $pr->currency_rate)) : floatval(abs($pr->rounding)),
						]);
					}
				}

				$coa = Coa::where('code','100.01.03.03.02')->where('company_id',$pr->company_id)->first();

				foreach($pr->paymentRequestCross as $row){
					JournalDetail::create([
						'journal_id'                    => $query->id,
						'coa_id'                        => $coa->id,
						'account_id'                    => $coa->bp_journal ? $row->lookable->account_id : NULL,
						'type'                          => '2',
						'nominal'                       => floatval($row->nominal * $pr->currency_rate),
						'nominal_fc'					=> $pr->currency->type == '1' ? floatval($row->nominal * $pr->currency_rate) : floatval($row->nominal),
					]);
					CustomHelper::removeCountLimitCredit($row->lookable->account_id,floatval($row->nominal * $pr->currency_rate));
				}

				$pr->update([
					'status'	=> '3',
				]);
			}
			
		}elseif($table_name == 'outgoing_payments'){
			$op = OutgoingPayment::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $op->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'outgoing_payments',
				'lookable_id'	=> $op->id,
				'currency_id'	=> $op->currency_id,
				'currency_rate'	=> $op->currency_rate,
				'post_date'		=> $op->pay_date,
				'note'			=> $op->note,
				'status'		=> '3'
			]);

			$totalPay = $op->balance * $op->currency_rate;

			$balanceKurs = 0;
			$totalReal = 0;
			$totalMustPay = 0;

			$arrNote = [];

			foreach($op->paymentRequest->paymentRequestDetail as $row){
				$mustpay = 0;
				$balanceReal = 0;

				if($row->coa_id){
					if(self::checkArrayRaw($arrNote,$row->note) < 0){
						$arrNote[] = $row->note;
					}

					if($row->lookable_type == 'purchase_invoices'){
						$mustpay = $row->lookable->getTotalPaidExcept($row->id);
						$balanceReal = $row->nominal * $row->lookable->currency_rate;
						if($row->lookable->getTotalPaid() <= 0){
							$row->lookable->update([
								'status'	=> '3'
							]);
						}
					}elseif($row->lookable_type == 'fund_requests'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
						if($row->lookable->type == '1' && $row->lookable->document_status == '3'){
							CustomHelper::addCountLimitCredit($row->lookable->account_id,$balanceReal);
						}
					}elseif($row->lookable_type == 'fund_request_details'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
						if($row->lookable->fundRequest->type == '1' && $row->lookable->fundRequest->document_status == '3'){
							CustomHelper::addCountLimitCredit($row->lookable->fundRequest->account_id,$balanceReal);
						}
					}elseif($row->lookable_type == 'coas'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
					}elseif($row->lookable_type == 'purchase_down_payments'){
						$mustpay = $row->lookable->balancePaidExcept($row->id);
						$balanceReal = $row->lookable->balancePaidExcept($row->id) * $row->lookable->currency_rate;
						if($row->lookable->getTotalPaid() <= 0){
							$row->lookable->update([
								'status'	=> '3'
							]);
						}
					}elseif($row->lookable_type == 'marketing_order_memos'){
						$rowtotal = $row->lookable->balance();
						$mustpay = $rowtotal;
						$balanceReal = $rowtotal;
					}

					if($row->cost_distribution_id){
						$total = $balanceReal;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : NULL,
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : NULL,
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
								'account_id'                    => $row->coa->bp_journal ? $op->account_id : NULL,
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '1',
								'nominal'                       => floatval($nominal * $op->currency_rate),
								'nominal_fc'					=> $op->currency->type == '1' ? floatval($nominal * $op->currency_rate) : floatval($nominal),
							]);
						}
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'account_id'	=> $row->coa->bp_journal ? $op->account_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> floatval($balanceReal),
							'nominal_fc'	=> $op->currency->type == '1' ? floatval(round($mustpay * $op->currency_rate,2)) : floatval(round($mustpay,2)),
						]);
						if($row->lookable_type == 'marketing_order_memos'){
							CustomHelper::addCountLimitCredit($op->account_id,$balanceReal);
						}
					}
				}else{
					if($row->lookable_type == 'fund_requests'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
						if($row->lookable->type == '1' && $row->lookable->document_status == '3'){
							CustomHelper::addCountLimitCredit($row->lookable->account_id,$balanceReal);
						}
					}
				}

				$totalMustPay += $mustpay;
				$totalReal += $balanceReal;
			}

			foreach($op->paymentRequest->paymentRequestCost as $row){
				if($row->nominal_debit_fc > 0){
					if($row->cost_distribution_id){
						$total = $row->nominal_debit_fc;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : $row->place_id,
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : $row->line_id,
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : $row->machine_id,
								'account_id'                    => $row->coa->bp_journal ? $op->account_id : NULL,
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : $row->division_id,
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '1',
								'nominal'                       => floatval($nominal * $op->currency_rate),
								'nominal_fc'					=> floatval($nominal),
								'note'							=> $row->note,
								'note2'							=> $row->note2,
							]);
						}
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'account_id'	=> $row->coa->bp_journal ? $op->account_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> floatval($row->nominal_debit_fc * $op->currency_rate),
							'nominal_fc'	=> floatval($row->nominal_debit_fc),
							'note'			=> $row->note,
							'note2'			=> $row->note2,
						]);
					}
				}

				if($row->nominal_credit_fc > 0){
					if($row->cost_distribution_id){
						$total = $row->nominal_credit_fc;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : $row->place_id,
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : $row->line_id,
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : $row->machine_id,
								'account_id'                    => $row->coa->bp_journal ? $op->account_id : NULL,
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : $row->division_id,
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '2',
								'nominal'                       => floatval($nominal * $op->currency_rate),
								'nominal_fc'					=> floatval($nominal),
								'note'							=> $row->note,
								'note2'							=> $row->note2,
							]);
						}
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'account_id'	=> $row->coa->bp_journal ? $op->account_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> floatval($row->nominal_credit_fc * $op->currency_rate),
							'nominal_fc'	=> floatval($row->nominal_credit_fc),
							'note'			=> $row->note,
							'note2'			=> $row->note2,
						]);
					}
				}
			}

			if($op->rounding && $op->currency_rate == 1){
				$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$op->company_id)->first();
				#start journal rounding
				if($op->rounding > 0 || $op->rounding < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
						'type'			=> $op->rounding > 0 ? '1' : '2',
						'nominal'		=> floatval(abs($op->rounding * $op->currency_rate)),
						'nominal_fc'	=> $op->currency->type == '1' ? floatval(abs($op->rounding * $op->currency_rate)) : floatval(abs($op->rounding)),
					]);
				}
			}elseif($op->rounding > 0 && $op->currency_rate > 1){
				
			}

			#perbaiki disini
			if($op->balance >= $totalMustPay && $op->currency_rate > 1){
				$balanceKurs = $totalReal - $totalPay;
				if($balanceKurs < 0 || $balanceKurs > 0){
					$coaselisihkurs = Coa::where('code','700.01.01.01.02')->where('company_id',$op->company_id)->first();
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coaselisihkurs->id,
						'account_id'	=> $coaselisihkurs->bp_journal ? $op->account_id : NULL,
						'place_id'		=> $row->lookable_type == 'fund_requests' ? $row->lookable->place_id : NULL,
						'department_id'	=> $row->lookable_type == 'fund_requests' ? $row->lookable->department_id : NULL,
						'type'			=> $balanceKurs < 0  ? '1' : '2',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
					]);
				}
			}

			if($op->admin > 0){
				$coa_admin = Coa::where('code','701.01.01.01.04')->where('company_id',$op->company_id)->first();
				if($op->cost_distribution_id){
					$total = $op->admin;
					$lastIndex = count($op->costDistribution->costDistributionDetail) - 1;
					$accumulation = 0;
					foreach($op->costDistribution->costDistributionDetail as $key => $rowcost){
						if($key == $lastIndex){
							$nominal = $total - $accumulation;
						}else{
							$nominal = round(($rowcost->percentage / 100) * $total);
							$accumulation += $nominal;
						}
						JournalDetail::create([
							'journal_id'                    => $query->id,
							'cost_distribution_detail_id'   => $rowcost->id,
							'coa_id'                        => $coa_admin ? $coa_admin->id : NULL,
							'place_id'                      => $rowcost->place_id ? $rowcost->place_id : NULL,
							'line_id'                       => $rowcost->line_id ? $rowcost->line_id : NULL,
							'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
							'account_id'                    => $coa_admin->bp_journal ? $op->account_id : NULL,
							'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
							'warehouse_id'                  => $rowcost->warehouse_id ? $rowcost->warehouse_id : NULL,
							'type'                          => '1',
							'nominal'                       => floatval($nominal * $op->currency_rate),
							'nominal_fc'					=> $op->currency->type == '1' ? floatval($nominal * $op->currency_rate) : floatval($nominal),
						]);
					}
				}else{
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coa_admin ? $coa_admin->id : NULL,
						'account_id'	=> $coa_admin->bp_journal ? $op->account_id : NULL,
						'type'			=> '1',
						'nominal'		=> floatval($row->nominal * $op->currency_rate),
						'nominal_fc'	=> $op->currency->type == '1' ? floatval($row->nominal * $op->currency_rate) : floatval($row->nominal),
					]);
				}
			}

			foreach($op->paymentRequest->paymentRequestCross as $row){
				$coa = Coa::where('code','100.01.03.03.02')->where('company_id',$op->company_id)->first();
				JournalDetail::create([
					'journal_id'                    => $query->id,
					'coa_id'                        => $coa->id,
					'account_id'                    => $coa->bp_journal ? $row->lookable->account_id : NULL,
					'type'                          => '2',
					'nominal'                       => floatval($row->nominal * $op->currency_rate),
					'nominal_fc'					=> $op->currency->type == '1' ? floatval($row->nominal * $op->currency_rate) : floatval($row->nominal),
				]);
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $op->coa_source_id,
				'account_id'	=> $op->coaSource->bp_journal ? $op->account_id : NULL,
				'type'			=> '2',
				'nominal'		=> floatval($totalPay),
				'nominal_fc'	=> $op->currency->type == '1' ? floatval($totalPay) : floatval($totalPay / $op->currency_rate),
			]);

			$op->paymentRequest->update([
				'status'	=> '3',
			]);

		}elseif($table_name == 'good_receives'){

			$gr = GoodReceive::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $gr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'good_receives',
				'lookable_id'	=> $gr->id,
				'currency_id'	=> $gr->currency_id,
				'currency_rate'	=> $gr->currency_rate,
				'post_date'		=> $gr->post_date,
				'note'			=> $gr->code,
				'status'		=> '3'
			]);

			foreach($gr->goodReceiveDetail as $row){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->item->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'line_id'		=> $row->line_id ? $row->line_id : NULL,
					'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
					'department_id'	=> $row->department_id ? $row->department_id : NULL,
					'warehouse_id'	=> $row->warehouse_id,
					'project_id'	=> $row->project_id ? $row->project_id : NULL,
					'type'			=> '1',
					'nominal'		=> $row->total * $gr->currency_rate,
					'nominal_fc'	=> $gr->currency->type == '1' ? $row->total * $gr->currency_rate : $row->total,
					'item_id'		=> $row->item_id,
				]);

				if($row->cost_distribution_id){
					$total = $row->total * $gr->currency_rate;
					$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
					$accumulation = 0;
					foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
						if($key == $lastIndex){
							$nominal = $total - $accumulation;
						}else{
							$nominal = round(($rowcost->percentage / 100) * $total);
							$accumulation += $nominal;
						}
						JournalDetail::create([
							'journal_id'                    => $query->id,
							'cost_distribution_detail_id'   => $rowcost->id,
							'coa_id'                        => $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa_id : $row->coa_id,
							'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_cost_id ?? NULL),
							'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
							'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
							'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->department_id ?? NULL),
							'project_id'					=> $row->project_id ? $row->project_id : NULL,
							'type'                          => '2',
							'nominal'						=> $nominal,
							'nominal_fc'					=> $nominal,
						]);
					}
				}else{
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa_id : $row->coa_id,
						'place_id'		=> $row->place_cost_id ? $row->place_cost_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->total * $gr->currency_rate,
						'nominal_fc'	=> $gr->currency->type == '1' ? $row->total * $gr->currency_rate : $row->total,
					]);
				}

				self::sendCogs('good_receives',
					$gr->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total * $gr->currency_rate,
					'IN',
					$gr->post_date,
					$row->area_id,
					$row->item_shading_id ? $row->item_shading_id : NULL,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					'IN',
					$row->area_id ? $row->area_id : NULL,
					$row->item_shading_id ? $row->item_shading_id : NULL,
				);
			}

			$gr->update([
				'status'	=> '3',
			]);
		}elseif($table_name == 'marketing_order_returns'){
			$mor = MarketingOrderReturn::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $mor->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'marketing_order_returns',
				'lookable_id'	=> $mor->id,
				'post_date'		=> $mor->post_date,
				'note'			=> $mor->code,
				'status'		=> '3'
			]);

			$coahpp = Coa::where('code','500.01.01.01.01')->where('company_id',$mor->company_id)->first();

			foreach($mor->marketingOrderReturnDetail as $row){

				$hpp = $row->marketingOrderDeliveryDetail->getPriceHpp() * $row->qty * $row->item->sell_convert;

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'account_id'	=> $mor->account_id,
					'coa_id'		=> $row->item->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'item_id'		=> $row->item_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $hpp,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'account_id'	=> $coahpp->bp_journal ? $mor->account_id : NULL,
					'coa_id'		=> $coahpp->id,
					'place_id'		=> $row->place_id,
					'item_id'		=> $row->item_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '2',
					'nominal'		=> $hpp,
				]);

				self::sendCogs('marketing_order_returns',
					$mor->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty * $row->item->sell_convert,
					$hpp,
					'IN',
					$mor->post_date,
					NULL,
					NULL,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty * $row->item->sell_convert,
					'IN',
					NULL,
					NULL,
				);
			}

		}elseif($table_name == 'good_returns'){

			$gr = GoodReturnPO::find($table_id);

			$arrNote = [];
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $gr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'good_returns',
				'lookable_id'	=> $gr->id,
				'post_date'		=> $gr->post_date,
				'note'			=> $gr->note,
				'status'		=> '3'
			]);

			$coa_credit = Coa::where('code','200.01.03.01.02')->where('company_id',$gr->company_id)->first();

			foreach($gr->goodReturnPODetail as $row){
				if(self::checkArrayRaw($arrNote,$row->goodReceiptDetail->goodReceipt->code) < 0){
					$arrNote[] = $row->goodReceiptDetail->goodReceipt->code;
				}

				$rowtotal = $row->getRowTotal() * $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_rate;

				if($coa_credit){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coa_credit->id,
						'place_id'		=> $row->goodReceiptDetail->place_id,
						'item_id'		=> $row->item_id,
						'account_id'	=> $coa_credit->bp_journal ? $row->goodReturnPO->account_id : NULL,
						'department_id'	=> $row->goodReceiptDetail->department_id ? $row->goodReceiptDetail->department_id : NULL,
						'warehouse_id'	=> $row->goodReceiptDetail->warehouse_id,
						'project_id'	=> $row->goodReceiptDetail->purchaseOrderDetail->project_id ? $row->goodReceiptDetail->purchaseOrderDetail->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $rowtotal,
						'nominal_fc'	=> $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? $rowtotal : $row->getRowTotal(),
					]);
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->item->itemGroup->coa_id,
					'place_id'		=> $row->goodReceiptDetail->place_id,
					'item_id'		=> $row->item_id,
					'department_id'	=> $row->goodReceiptDetail->department_id ? $row->goodReceiptDetail->department_id : NULL,
					'warehouse_id'	=> $row->goodReceiptDetail->warehouse_id,
					'project_id'	=> $row->goodReceiptDetail->purchaseOrderDetail->project_id ? $row->goodReceiptDetail->purchaseOrderDetail->project_id : NULL,
					'type'			=> '2',
					'nominal'		=> $rowtotal,
					'nominal_fc'	=> $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency->type == '1' ? $rowtotal : $row->getRowTotal(),
				]);

				self::sendCogs('good_returns',
					$gr->id,
					$row->goodReceiptDetail->place->company_id,
					$row->goodReceiptDetail->place_id,
					$row->goodReceiptDetail->warehouse_id,
					$row->item_id,
					$row->qtyConvert(),
					$rowtotal,
					'OUT',
					$gr->post_date,
					NULL,
					NULL,
				);

				self::sendStock(
					$row->goodReceiptDetail->place_id,
					$row->goodReceiptDetail->warehouse_id,
					$row->item_id,
					$row->qtyConvert(),
					'OUT',
					NULL,
					NULL,
				);

				$row->goodReceiptDetail->goodReceipt->updateRootDocumentStatusProcess();
			}

		}elseif($table_name == 'marketing_order_delivery_processes'){

			

		}elseif($table_name == 'good_issues'){

			$gr = GoodIssue::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $gr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> 'good_issues',
				'lookable_id'	=> $gr->id,
				'post_date'		=> $gr->post_date,
				'note'			=> $gr->note,
				'status'		=> '3'
			]);

			foreach($gr->goodIssueDetail as $row){

				$total = round($row->itemStock->priceDate($gr->post_date) * $row->qty,2);

				if($row->cost_distribution_id){
					$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
					$accumulation = 0;
					foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
						if($key == $lastIndex){
							$nominal = $total - $accumulation;
						}else{
							$nominal = round(($rowcost->percentage / 100) * $total);
							$accumulation += $nominal;
						}
						JournalDetail::create([
							'journal_id'                    => $query->id,
							'cost_distribution_detail_id'   => $rowcost->id,
							'coa_id'						=> $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa_id : $row->coa_id,
							'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_id ?? NULL),
							'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
							'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
							'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->department_id ?? NULL),
							'project_id'					=> $row->project_id ? $row->project_id : NULL,
							'type'                          => '1',
							'nominal'						=> $nominal,
							'nominal_fc'					=> $nominal,
						]);
					}
				}else{
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->inventoryCoa()->exists() ? $row->inventoryCoa->coa_id : $row->coa_id,
						'place_id'		=> $row->place()->exists() ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $total,
						'nominal_fc'	=> $total,
					]);
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->itemStock->item->itemGroup->coa_id,
					'place_id'		=> $row->itemStock->place_id,
					'warehouse_id'	=> $row->itemStock->warehouse_id,
					'line_id'		=> $row->line_id ? $row->line_id : NULL,
					'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
					'department_id'	=> $row->department_id ? $row->department_id : NULL,
					'project_id'	=> $row->project_id ? $row->project_id : NULL,
					'type'			=> '2',
					'nominal'		=> $total,
					'nominal_fc'	=> $total,
				]);

				self::sendCogs('good_issues',
					$gr->id,
					$row->itemStock->place->company_id,
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->itemStock->item_id,
					$row->qty,
					$total,
					'OUT',
					$gr->post_date,
					$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
					$row->itemStock->item_shading_id ? $row->itemStock->item_shading_id : NULL,
				);

				self::sendStock(
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->itemStock->item_id,
					$row->qty,
					'OUT',
					$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
					$row->itemStock->item_shading_id ? $row->itemStock->item_shading_id : NULL,
				);

				$row->update([
					'total'	=> $total,
				]);
			}

			if($gr){
				$gr->updateRootDocumentStatusDone();
				$gr->update([
					'status' => '3'
				]);
			}

		}elseif($table_name == 'good_return_issues'){
			
			$gr = GoodReturnIssue::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $gr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $gr->getTable(),
				'lookable_id'	=> $gr->id,
				'post_date'		=> $gr->post_date,
				'note'			=> $gr->code,
				'status'		=> '3'
			]);

			foreach($gr->goodReturnIssueDetail as $row){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->goodIssueDetail->itemStock->item->itemGroup->coa_id,
					'place_id'		=> $row->goodIssueDetail->itemStock->place_id,
					'item_id'		=> $row->goodIssueDetail->itemStock->item_id,
					'warehouse_id'	=> $row->goodIssueDetail->itemStock->warehouse_id,
					'line_id'		=> $row->goodIssueDetail->line_id ? $row->goodIssueDetail->line_id : NULL,
					'machine_id'	=> $row->goodIssueDetail->machine_id ? $row->goodIssueDetail->machine_id : NULL,
					'department_id'	=> $row->goodIssueDetail->department_id ? $row->goodIssueDetail->department_id : NULL,
					'project_id'	=> $row->goodIssueDetail->project_id ? $row->goodIssueDetail->project_id : NULL,
					'type'			=> '1',
					'nominal'		=> $row->total,
					'nominal_fc'	=> $row->total,
				]);

				if($row->goodIssueDetail->cost_distribution_id){
					$total = $row->total;
					$lastIndex = count($row->goodIssueDetail->costDistribution->costDistributionDetail) - 1;
					$accumulation = 0;
					foreach($row->goodIssueDetail->costDistribution->costDistributionDetail as $key => $rowcost){
						if($key == $lastIndex){
							$nominal = $total - $accumulation;
						}else{
							$nominal = round(($rowcost->percentage / 100) * $total);
							$accumulation += $nominal;
						}
						JournalDetail::create([
							'journal_id'                    => $query->id,
							'cost_distribution_detail_id'   => $rowcost->id,
							'coa_id'                        => $row->goodIssueDetail->inventoryCoa()->exists() ? $row->goodIssueDetail->inventoryCoa->coa_id : $row->goodIssueDetail->coa_id,
							'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_id ?? NULL),
							'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
							'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
							'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->department_id ?? NULL),
							'project_id'					=> $row->goodIssueDetail->project_id ? $row->goodIssueDetail->project_id : NULL,
							'type'                          => '2',
							'nominal'						=> $nominal,
							'nominal_fc'					=> $nominal,
						]);
					}
				}else{
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->goodIssueDetail->inventoryCoa()->exists() ? $row->goodIssueDetail->inventoryCoa->coa_id : $row->goodIssueDetail->coa_id,
						'place_id'		=> $row->goodIssueDetail->place()->exists() ? $row->goodIssueDetail->place_id : NULL,
						'item_id'		=> $row->goodIssueDetail->itemStock->item_id,
						'line_id'		=> $row->goodIssueDetail->line_id ? $row->goodIssueDetail->line_id : NULL,
						'machine_id'	=> $row->goodIssueDetail->machine_id ? $row->goodIssueDetail->machine_id : NULL,
						'department_id'	=> $row->goodIssueDetail->department_id ? $row->goodIssueDetail->department_id : NULL,
						'project_id'	=> $row->goodIssueDetail->project_id ? $row->goodIssueDetail->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->total,
						'nominal_fc'	=> $row->total,
					]);
				}

				self::sendCogs($gr->getTable(),
					$gr->id,
					$row->goodIssueDetail->itemStock->place->company_id,
					$row->goodIssueDetail->itemStock->place_id,
					$row->goodIssueDetail->itemStock->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total,
					'IN',
					$gr->post_date,
					$row->goodIssueDetail->itemStock->area_id ? $row->goodIssueDetail->itemStock->area_id : NULL,
					$row->goodIssueDetail->itemStock->item_shading_id ? $row->goodIssueDetail->itemStock->item_shading_id : NULL,
				);

				self::sendStock(
					$row->goodIssueDetail->itemStock->place_id,
					$row->goodIssueDetail->itemStock->warehouse_id,
					$row->item_id,
					$row->qty,
					'IN',
					$row->goodIssueDetail->itemStock->area_id ? $row->goodIssueDetail->itemStock->area_id : NULL,
					$row->goodIssueDetail->itemStock->item_shading_id ? $row->goodIssueDetail->itemStock->item_shading_id : NULL,
				);
			}

		}elseif($table_name == 'landed_costs'){

			$lc = LandedCost::find($data->id);
			
			if($lc){
				$arrNote = [];

				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $lc->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> 'landed_costs',
					'lookable_id'	=> $lc->id,
					'post_date'		=> $data->post_date,
					'note'			=> 'LANDED COST - '.$data->code,
					'status'		=> '3'
				]);

				foreach($lc->landedCostDetail as $rowdetail){

					$itemdata = ItemCogs::where('place_id',$rowdetail->place_id)->where('item_id',$rowdetail->item_id)->orderByDesc('date')->orderByDesc('id')->first();
					if($itemdata){
						if($itemdata->qty_final > 0){
							self::sendCogs('landed_costs',
								$lc->id,
								$rowdetail->place->company_id,
								$rowdetail->place_id,
								$rowdetail->warehouse_id,
								$rowdetail->item_id,
								0,
								$rowdetail->nominal * $lc->currency_rate,
								'IN',
								$lc->post_date,
								NULL,
								NULL,
							);

							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
								'place_id'		=> $rowdetail->place_id,
								'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
								'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
								'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
								'warehouse_id'	=> $rowdetail->warehouse_id,
								'item_id'		=> $rowdetail->item_id,
								'type'			=> '1',
								'nominal'		=> $rowdetail->nominal * $lc->currency_rate,
								'nominal_fc'	=> $lc->currency->type == '1' ? $rowdetail->nominal * $lc->currency_rate : $rowdetail->nominal,
							]);
						}else{
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $rowdetail->coa_id,
								'place_id'		=> $rowdetail->place_id,
								'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
								'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
								'account_id'	=> $rowdetail->coa->bp_journal ? $lc->account_id : NULL,
								'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
								'warehouse_id'	=> $rowdetail->warehouse_id,
								'item_id'		=> $rowdetail->item_id,
								'type'			=> '1',
								'nominal'		=> $rowdetail->nominal * $lc->currency_rate,
								'nominal_fc'	=> $lc->currency->type == '1' ? $rowdetail->nominal * $lc->currency_rate : $rowdetail->nominal,
							]);
						}
					}
				}

				foreach($lc->landedCostFeeDetail as $rowdetail){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $rowdetail->landedCostFee->coa_id,
						'account_id'	=> $rowdetail->landedCostFee->coa->bp_journal ? $lc->account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $rowdetail->total * $lc->currency_rate,
						'nominal_fc'	=> $lc->currency->type == '1' ? $rowdetail->total * $lc->currency_rate : $rowdetail->total,
						'note'			=> $rowdetail->landedCostFee->name,
					]);
				}
			}
		}elseif($table_name == 'inventory_revaluations'){

			$ir = InventoryRevaluation::find($data->id);
			
			if($ir){
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $ir->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> $ir->getTable(),
					'lookable_id'	=> $ir->id,
					'post_date'		=> $data->post_date,
					'note'			=> $data->code,
					'status'		=> '3'
				]);

				foreach($ir->inventoryRevaluationDetail as $rowdetail){
					self::sendCogs($ir->getTable(),
						$ir->id,
						$rowdetail->place->company_id,
						$rowdetail->place_id,
						$rowdetail->warehouse_id,
						$rowdetail->item_id,
						0,
						$rowdetail->nominal,
						'IN',
						$ir->post_date,
						$rowdetail->itemStock->area()->exists() ? $rowdetail->itemStock->area_id : NULL,
						$rowdetail->itemStock->itemShading()->exists() ? $rowdetail->itemStock->item_shading_id : NULL,
					);
					
					if($rowdetail->nominal < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $rowdetail->coa_id,
							'place_id'		=> $rowdetail->place_id,
							'warehouse_id'	=> $rowdetail->warehouse_id,
							'item_id'		=> $rowdetail->item_id,
							'line_id'		=> $rowdetail->line_id,
							'machine_id'	=> $rowdetail->machine_id,
							'department_id'	=> $rowdetail->department_id,
							'project_id'	=> $rowdetail->project_id,
							'type'			=> '1',
							'nominal'		=> -1 * $rowdetail->nominal,
							'nominal_fc'	=> -1 * $rowdetail->nominal,
						]);

						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
							'place_id'		=> $rowdetail->place_id,
							'warehouse_id'	=> $rowdetail->warehouse_id,
							'item_id'		=> $rowdetail->item_id,
							'line_id'		=> $rowdetail->line_id,
							'machine_id'	=> $rowdetail->machine_id,
							'department_id'	=> $rowdetail->department_id,
							'project_id'	=> $rowdetail->project_id,
							'type'			=> '2',
							'nominal'		=> -1 * $rowdetail->nominal,
							'nominal_fc'	=> -1 * $rowdetail->nominal
						]);
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
							'place_id'		=> $rowdetail->place_id,
							'warehouse_id'	=> $rowdetail->warehouse_id,
							'item_id'		=> $rowdetail->item_id,
							'line_id'		=> $rowdetail->line_id,
							'machine_id'	=> $rowdetail->machine_id,
							'department_id'	=> $rowdetail->department_id,
							'project_id'	=> $rowdetail->project_id,
							'type'			=> '1',
							'nominal'		=> $rowdetail->nominal,
							'nominal_fc'	=> $rowdetail->nominal,
						]);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $rowdetail->coa_id,
							'place_id'		=> $rowdetail->place_id,
							'warehouse_id'	=> $rowdetail->warehouse_id,
							'item_id'		=> $rowdetail->item_id,
							'line_id'		=> $rowdetail->line_id,
							'machine_id'	=> $rowdetail->machine_id,
							'department_id'	=> $rowdetail->department_id,
							'project_id'	=> $rowdetail->project_id,
							'type'			=> '2',
							'nominal'		=> $rowdetail->nominal,
							'nominal_fc'	=> $rowdetail->nominal,
						]);
					}
				}
			}
		}elseif($table_name == 'fund_requests'){
		
		}elseif($table_name == 'overtime_requests'){
			
			$OR = OvertimeRequest::find($table_id);
			if($OR->schedule()->exists()){
				$query_schedule = EmployeeSchedule::find($OR->schedule_id);
				$query_schedule->status = '5';

				$query_schedule->save();
			}
			
		}
		elseif($table_name == 'capitalizations'){		
			$arrdata = get_object_vars($data);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $data->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> $data->currency_id,
				'currency_rate'	=> $data->currency_rate,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note,
				'status'		=> '3'
			]);
			
			$cp = Capitalization::find($data->id);
			if($cp){
				foreach($cp->capitalizationDetail as $row){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->asset->assetGroup->coa_id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->total * $cp->currency_rate,
						'nominal_fc'	=> $cp->currency->type == '1' ? $row->total * $cp->currency_rate : $row->total,
					]);

					$coaAyatSilangPembelianAset = Coa::where('code','100.01.01.99.04')->where('company_id',$row->asset->place->company_id)->first();

					if($row->cost_distribution_id){
						$total = $row->total;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $coaAyatSilangPembelianAset->id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_id ?? NULL),
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->department_id ?? NULL),
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '1',
								'nominal'                       => $nominal * $cp->currency_rate,
								'nominal_fc'					=> $cp->currency->type == '1' ? $nominal * $cp->currency_rate : $nominal,
							]);
						}
					}else{
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaAyatSilangPembelianAset->id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->total * $cp->currency_rate,
							'nominal_fc'	=> $cp->currency->type == '1' ? $row->total * $cp->currency_rate : $row->total,
						]);
					}

					$asset = $row->asset;
                    if($asset){
                        $asset->update([
                            'date'                  => $cp->post_date,
                            'nominal'               => $row->total * $cp->currency_rate,
                            'accumulation_total'    => 0,
                            'book_balance'          => $row->total * $cp->currency_rate,
                            'count_balance'         => $asset->assetGroup->depreciation_period,
                        ]);
                    }
				}

				$cp->update([
					'status'	=> '3'
				]);
			}
		}elseif($table_name == 'inventory_transfer_outs'){
			
			$ito = InventoryTransferOut::find($table_id);

			/* if(($ito->place_from !== $ito->place_to) || ($ito->place_from == $ito->place_to && $ito->warehouse_from !== $ito->warehouse_to)){ */
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $ito->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'post_date'		=> $data->post_date,
					'note'			=> 'TRANSFER OUT - '.$data->code,
					'status'		=> '3'
				]);
	
				$coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$ito->company_id)->first();
	
				foreach($ito->inventoryTransferOutDetail as $rowdetail){
					$priceout = $rowdetail->item->priceNow($rowdetail->itemStock->place_id,$ito->post_date);
					$nominal = round($rowdetail->qty * $priceout,2);
	
					$rowdetail->update([
						'price'	=> $priceout,
						'total'	=> $nominal,
					]);
					
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coabdp ? $coabdp->id : NULL,
						'place_id'		=> $rowdetail->itemStock->place_id,
						'item_id'		=> $rowdetail->item_id,
						'warehouse_id'	=> $rowdetail->itemStock->warehouse_id,
						'type'			=> '1',
						'nominal'		=> $nominal,
						'nominal_fc'	=> $nominal,
					]);
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
						'place_id'		=> $rowdetail->itemStock->place_id,
						'item_id'		=> $rowdetail->item_id,
						'warehouse_id'	=> $rowdetail->itemStock->warehouse_id,
						'type'			=> '2',
						'nominal'		=> $nominal,
						'nominal_fc'	=> $nominal,
					]);
				}
			/* } */

			foreach($ito->inventoryTransferOutDetail as $rowdetail){
				$priceout = $rowdetail->item->priceNow($rowdetail->itemStock->place_id,$ito->post_date);
				$nominal = round($rowdetail->qty * $priceout,2);

				$rowdetail->update([
					'price'	=> $priceout,
					'total'	=> $nominal,
				]);

				self::sendCogs('inventory_transfer_outs',
					$ito->id,
					$rowdetail->itemStock->place->company_id,
					$rowdetail->itemStock->place_id,
					$rowdetail->itemStock->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					$nominal,
					'OUT',
					$ito->post_date,
					$rowdetail->itemStock->area_id,
					$rowdetail->itemStock->item_shading_id,
				);

				self::sendStock(
					$rowdetail->itemStock->place_id,
					$rowdetail->itemStock->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					'OUT',
					$rowdetail->itemStock->area_id,
					$rowdetail->itemStock->item_shading_id,
				);
			}
			
		}elseif($table_name == 'inventory_transfer_ins'){

			$iti = InventoryTransferIn::find($table_id);

			/* if(($iti->inventoryTransferOut->place_from !== $iti->InventoryTransferOut->place_to) || ($iti->inventoryTransferOut->place_from == $iti->inventoryTransferOut->place_to && $iti->inventoryTransferOut->warehouse_from !== $iti->inventoryTransferOut->warehouse_to)){ */
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $iti->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'post_date'		=> $data->post_date,
					'note'			=> 'TRANSFER IN - '.$data->code,
					'status'		=> '3'
				]);
	
				$coabdp = Coa::where('code','100.01.04.05.01')->where('company_id',$iti->company_id)->first();
	
				foreach($iti->inventoryTransferOut->inventoryTransferOutDetail as $rowdetail){
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
						'place_id'		=> $iti->inventoryTransferOut->place_to,
						'item_id'		=> $rowdetail->item_id,
						'warehouse_id'	=> $iti->inventoryTransferOut->warehouse_to,
						'type'			=> '1',
						'nominal'		=> $rowdetail->total,
						'nominal_fc'	=> $rowdetail->total,
					]);
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coabdp ? $coabdp->id : NULL,
						'place_id'		=> $iti->inventoryTransferOut->place_from,
						'item_id'		=> $rowdetail->item_id,
						'warehouse_id'	=> $iti->inventoryTransferOut->warehouse_from,
						'type'			=> '2',
						'nominal'		=> $rowdetail->total,
						'nominal_fc'	=> $rowdetail->total,
					]);
				}
			/* } */

			foreach($iti->inventoryTransferOut->inventoryTransferOutDetail as $rowdetail){
				self::sendCogs('inventory_transfer_ins',
					$iti->id,
					$iti->company_id,
					$iti->inventoryTransferOut->place_to,
					$iti->inventoryTransferOut->warehouse_to,
					$rowdetail->item_id,
					$rowdetail->qty,
					$rowdetail->total,
					'IN',
					$iti->post_date,
					$rowdetail->area_id,
					$rowdetail->itemStock->item_shading_id,
				);

				self::sendStock(
					$iti->inventoryTransferOut->place_to,
					$iti->inventoryTransferOut->warehouse_to,
					$rowdetail->item_id,
					$rowdetail->qty,
					'IN',
					$rowdetail->area_id,
					$rowdetail->itemStock->item_shading_id,
				);
			}

		}elseif($table_name == 'depreciations'){

			$dpr = Depreciation::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $dpr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note,
				'status'		=> '3'
			]);

			foreach($dpr->depreciationDetail as $row){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->cost_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '1',
					'nominal'		=> $row->nominal,
					'nominal_fc'	=> $row->nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->depreciation_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '2',
					'nominal'		=> $row->nominal,
					'nominal_fc'	=> $row->nominal,
				]);
				
				self::updateBalanceAsset($row->asset_id,$row->nominal,'OUT',$table_name);
			}

			$dpr->update([
				'status'	=> '3',
			]);

		}elseif($table_name == 'work_orders'){

		}elseif($table_name == 'request_spareparts'){

		}elseif($table_name == 'purchase_memos'){

			$pm = PurchaseMemo::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pm->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pm->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$pm->company_id)->first();

			$type = '';
			$currency_rate = 1;
			foreach($pm->purchaseMemoDetail as $row){
				$coacode = '';

				if($row->lookable_type == 'purchase_invoice_details'){
					if($row->wtax > 0){
						$wtax = 0;
						if($row->lookable->lookable_type == 'coas'){
							$wtax = $row->wtax;
						}elseif($row->lookable->lookable_type == 'purchase_order_details'){
							$wtax = $row->wtax * $row->lookable->lookable->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrder->currency->type;
						}elseif($row->lookable->lookable_type == 'landed_cost_fee_details'){
							$wtax = $row->wtax * $row->lookable->lookable->landedCost->currency_rate;
							$currency_rate = $row->lookable->lookable->landedCost->currency_rate;
							$type = $row->lookable->lookable->landedCost->currency->type;
						}else{
							$wtax = $row->wtax * $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency->type;
						}
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'place_id'		=> $row->lookable->place_id ? $row->lookable->place_id : NULL,
							'line_id'		=> $row->lookable->line_id ? $row->lookable->line_id : NULL,
							'machine_id'	=> $row->lookable->line_id ? $row->lookable->line_id : NULL,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $row->lookable->purchaseInvoice->account_id : NULL,
							'department_id'	=> $row->lookable->department_id ? $row->lookable->department_id : NULL,
							'warehouse_id'	=> $row->lookable->warehouse_id ? $row->lookable->warehouse_id : NULL,
							'project_id'	=> $row->lookable->project_id ? $row->lookable->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> $wtax,
							'nominal_fc'	=> $type == '1' || $type == '' ? $wtax : $row->wtax,
							'note'			=> $pm->return_tax_no,
							'note2'			=> date('d/m/Y',strtotime($pm->return_date))
						]);
					}
					
					if($row->grandtotal > 0){
						$grandtotal = 0;
						$realgrandtotal = 0;
						$place_id = NULL;
						$line = NULL;
						$machine = NULL;
						$department = NULL;
						$project_id = NULL;
						if($row->lookable->lookable_type == 'coas'){
							$grandtotal = $row->grandtotal;
						}elseif($row->lookable->lookable_type == 'purchase_order_details'){
							$grandtotal = $row->grandtotal * $row->lookable->lookable->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrder->currency->type;
							$place_id = $row->lookable->place_id;
							$line = $row->lookable->line_id;
							$machine = $row->lookable->machine_id;
							$department = $row->lookable->department_id;
							$project_id = $row->lookable->project_id;
						}elseif($row->lookable->lookable_type == 'landed_cost_fee_details'){
							$grandtotal = $row->grandtotal * $row->lookable->lookable->landedCost->currency_rate;
							$currency_rate = $row->lookable->lookable->landedCost->currency_rate;
							$type = $row->lookable->lookable->landedCost->currency->type;
						}else{
							$grandtotal = $row->grandtotal * $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency->type;
						}
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coahutangusaha->id,
							'account_id'	=> $coahutangusaha->bp_journal ? $row->lookable->purchaseInvoice->account_id : NULL,
							'type'			=> '1',
							'place_id'		=> $place_id,
							'line_id'		=> $line,
							'machine_id'	=> $machine,
							'department_id'	=> $department,
							'project_id'	=> $project_id,
							'nominal'		=> $grandtotal,
							'nominal_fc'	=> $type == '1' || $type == '' ? $grandtotal : $row->grandtotal,
						]);
					}
					
					if($row->total > 0){
						$total = 0;
						if($row->lookable->lookable_type == 'coas'){
							$total = $row->total;
						}elseif($row->lookable->lookable_type == 'purchase_order_details'){
							$total = $row->total * $row->lookable->lookable->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrder->currency->type;

							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $row->lookable->lookable->coa_id,
								'place_id'		=> $row->lookable->lookable->place_id ? $row->lookable->lookable->place_id : NULL,
								'line_id'		=> $row->lookable->lookable->line_id ? $row->lookable->lookable->line_id : NULL,
								'machine_id'	=> $row->lookable->lookable->machine_id ? $row->lookable->lookable->machine_id : NULL,
								'department_id'	=> $row->lookable->lookable->department_id ? $row->lookable->lookable->department_id : NULL,
								'warehouse_id'	=> $row->lookable->lookable->warehouse_id ? $row->lookable->lookable->warehouse_id : NULL,
								'project_id'	=> $row->lookable->lookable->project_id ? $row->lookable->lookable->project_id : NULL,
								'type'			=> '2',
								'nominal'		=> $total,
								'nominal_fc'	=> $type == '1' || $type == '' ? $total : $row->total,
							]);
						}elseif($row->lookable->lookable_type == 'landed_cost_fee_details'){
							$total = $row->total * $row->lookable->lookable->landedCost->currency_rate;
							$currency_rate = $row->lookable->lookable->landedCost->currency_rate;
							$type = $row->lookable->lookable->landedCost->currency->type;
						}elseif($row->lookable->lookable_type == 'good_receipt_details'){
							$total = $row->total * $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$currency_rate = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
							$type = $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency->type;

							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $row->lookable->lookable->item->itemGroup->coa_id,
								'place_id'		=> $row->lookable->lookable->place_id,
								'line_id'		=> $row->lookable->lookable->line_id ? $row->lookable->lookable->line_id : NULL,
								'machine_id'	=> $row->lookable->lookable->machine_id ? $row->lookable->lookable->machine_id : NULL,
								'department_id'	=> $row->lookable->lookable->department_id ? $row->lookable->lookable->department_id : NULL,
								'warehouse_id'	=> $row->lookable->lookable->warehouse_id,
								'project_id'	=> $row->lookable->lookable->purchaseOrderDetail->project_id ? $row->lookable->lookable->purchaseOrderDetail->project_id : NULL,
								'type'			=> '2',
								'nominal'		=> $total,
								'nominal_fc'	=> $type == '1' || $type == '' ? $total : $row->total,
							]);

							self::sendCogs('purchase_memos',
								$pm->id,
								$pm->company_id,
								$row->lookable->lookable->place_id,
								$row->lookable->lookable->warehouse_id,
								$row->lookable->lookable->item_id,
								round($row->qty * $row->lookable->lookable->qty_conversion,3),
								$total,
								'OUT',
								$pm->post_date,
								NULL,
								NULL,
							);

							self::sendStock(
								$row->lookable->lookable->place_id,
								$row->lookable->lookable->warehouse_id,
								$row->lookable->lookable->item_id,
								round($row->qty * $row->lookable->lookable->qty_conversion,3),
								'OUT',
								NULL,
								NULL,
							);
						}
					}

					if($row->tax > 0){
						$tax = 0;
						if($row->lookable->lookable_type == 'coas'){
							$tax = $row->tax;
						}elseif($row->lookable->lookable_type == 'purchase_order_details'){
							$tax = $row->tax * $row->lookable->lookable->purchaseOrder->currency_rate;
						}elseif($row->lookable->lookable_type == 'landed_cost_fee_details'){
							$tax = $row->tax * $row->lookable->lookable->landedCost->currency_rate;
						}else{
							$tax = $row->tax * $row->lookable->lookable->purchaseOrderDetail->purchaseOrder->currency_rate;
						}
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'place_id'		=> $row->lookable->place_id ? $row->lookable->place_id : NULL,
							'line_id'		=> $row->lookable->line_id ? $row->lookable->line_id : NULL,
							'machine_id'	=> $row->lookable->line_id ? $row->lookable->line_id : NULL,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $row->lookable->purchaseInvoice->account_id : NULL,
							'department_id'	=> $row->lookable->department_id ? $row->lookable->department_id : NULL,
							'warehouse_id'	=> $row->lookable->warehouse_id ? $row->lookable->warehouse_id : NULL,
							'project_id'	=> $row->lookable->project_id ? $row->lookable->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $tax,
							'nominal_fc'	=> $type == '1' || $type == '' ? $tax : $row->tax,
							'note'			=> $pm->return_tax_no,
							'note2'			=> date('d/m/Y',strtotime($pm->return_date))
						]);
					}
				}

				if($row->lookable_type == 'purchase_down_payments'){
					$coacode = '100.01.07.01.01';
					$coamodel = Coa::where('code',$coacode)->where('company_id',$pm->company_id)->first();
					$type = $row->lookable->currency->type;
					$currency_rate = $row->lookable->currency_rate;

					if($row->wtax > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $row->lookable->account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->wtax * $row->lookable->currency_rate,
							'nominal_fc'	=> $row->lookable->currency->type == '1' ? $row->wtax * $row->lookable->currency_rate : $row->wtax,
							'note'			=> $pm->return_tax_no,
							'note2'			=> date('d/m/Y',strtotime($pm->return_date))
						]);
					}
					
					if($row->grandtotal > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coahutangusaha->id,
							'account_id'	=> $coahutangusaha->bp_journal ? $row->lookable->account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->grandtotal * $row->lookable->currency_rate,
							'nominal_fc'	=> $row->lookable->currency->type == '1' ? $row->grandtotal * $row->lookable->currency_rate : $row->grandtotal,
						]);
					}

					if($row->total > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coamodel->id,
							'account_id'	=> $coamodel->bp_journal ? $row->lookable->account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->total * $row->lookable->currency_rate,
							'nominal_fc'	=> $row->lookable->currency->type == '1' ? $row->total * $row->lookable->currency_rate : $row->total,
						]);
					}

					if($row->tax > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $row->lookable->account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->tax * $row->lookable->currency_rate,
							'nominal_fc'	=> $row->lookable->currency->type == '1' ? $row->tax * $row->lookable->currency_rate : $row->tax,
							'note'			=> $pm->return_tax_no,
							'note2'			=> date('d/m/Y',strtotime($pm->return_date))
						]);
					}
				}
			}

			if($pm->rounding > 0 || $pm->rounding < 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coahutangusaha->id,
					'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
					'type'			=> '1',
					'nominal'		=> $pm->rounding > 0 ? $pm->rounding * $currency_rate : $pm->rounding * $currency_rate,
					'nominal_fc'	=> $type == '1' || $type == '' ? ($pm->rounding > 0 ? $pm->rounding * $currency_rate : $pm->rounding * $currency_rate) : $pm->rounding,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coarounding->id,
					'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $pm->rounding > 0 ? $pm->rounding : $pm->rounding,
					'nominal_fc'	=> $type == '1' || $type == '' ? ($pm->rounding > 0 ? $pm->rounding : $pm->rounding) : $pm->rounding,
				]);
			}

		}elseif($table_name == 'close_bills'){

			$cb = CloseBill::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $cb->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> isset($data->currency_id) ? $data->currency_id : NULL,
				'currency_rate'	=> isset($data->currency_rate) ? $data->currency_rate : NULL,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note,
				'status'		=> '3'
			]);

			$coapiutangbs = Coa::where('code','100.01.03.03.02')->where('company_id',$cb->company_id)->first();

			foreach($cb->closeBillDetail as $row){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapiutangbs->id,
					'account_id'	=> $coapiutangbs->bp_journal ? $row->outgoingPayment->account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $row->nominal * $cb->currency_rate,
					'nominal_fc'	=> $cb->currency->type == '1' || $cb->currency->type == '' ? $row->nominal * $cb->currency_rate : $row->nominal,
					'note'			=> $row->note,
				]);
				CustomHelper::removeCountLimitCredit($row->outgoingPayment->account_id,floatval($row->nominal * $cb->currency_rate));
			}

			foreach($cb->closeBillCost as $row){
				if($row->cost_distribution_id){
					if($row->nominal_debit_fc !== 0){
						$total = $row->nominal_debit_fc;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_id ?? NULL),
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->division_id ?? NULL),
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '1',
								'nominal'                       => floatval($nominal),
								'nominal_fc'					=> $cb->currency->type == '1' || $cb->currency->type == '' ? floatval($nominal * $cb->currency_rate) : floatval($nominal),
								'note'							=> $row->note,
								'note2'							=> $row->note2,
							]);
						}
					}
					if($row->nominal_credit_fc !== 0){
						$total = $row->nominal_credit_fc;
						$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
						$accumulation = 0;
						foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
							if($key == $lastIndex){
								$nominal = $total - $accumulation;
							}else{
								$nominal = round(($rowcost->percentage / 100) * $total);
								$accumulation += $nominal;
							}
							JournalDetail::create([
								'journal_id'                    => $query->id,
								'cost_distribution_detail_id'   => $rowcost->id,
								'coa_id'                        => $row->coa_id,
								'place_id'                      => $rowcost->place_id ? $rowcost->place_id : ($row->place_id ?? NULL),
								'line_id'                       => $rowcost->line_id ? $rowcost->line_id : ($row->line_id ?? NULL),
								'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : ($row->machine_id ?? NULL),
								'department_id'                 => $rowcost->department_id ? $rowcost->department_id : ($row->division_id ?? NULL),
								'project_id'					=> $row->project_id ? $row->project_id : NULL,
								'type'                          => '2',
								'nominal'                       => floatval($nominal),
								'nominal_fc'					=> $cb->currency->type == '1' || $cb->currency->type == '' ? floatval($nominal * $cb->currency_rate) : floatval($nominal),
								'note'							=> $row->note,
								'note2'							=> $row->note2,
							]);
						}
					}
				}else{
					if($row->nominal_debit_fc > 0 || $row->nominal_debit_fc < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> $row->place_id,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'department_id'	=> $row->division_id ? $row->division_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->nominal_debit,
							'nominal_fc'	=> $row->nominal_debit_fc,
							'note'			=> $row->note,
							'note2'			=> $row->note2,
						]);
					}
					if($row->nominal_credit_fc > 0 || $row->nominal_credit_fc < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> $row->place_id,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'department_id'	=> $row->division_id ? $row->division_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->nominal_credit,
							'nominal_fc'	=> $row->nominal_credit_fc,
							'note'			=> $row->note,
							'note2'			=> $row->note2,
						]);
					}
				}
			}

			$cb->update([
				'status'	=> '3'
			]);
			
		}elseif($table_name == 'marketing_order_invoices'){

			$moi = MarketingOrderInvoice::find($table_id);

			$coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$moi->company_id)->first();
			$coauangmuka = Coa::where('code','200.01.06.01.01')->where('company_id',$moi->company_id)->first();
			$coapenjualan = Coa::where('code','400.01.01.01.01')->where('company_id',$moi->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$moi->company_id)->first();

			$arrNote = [];

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $moi->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			$total = 0;
			$tax = 0;
			$total_after_tax = 0;
			$rounding = 0;
			$grandtotal = 0;
			$downpayment = 0;
			$balance = 0;
			$dp_total = 0;
			$dp_tax = 0;
			$coa_sale_id = null;

			foreach($moi->marketingOrderInvoiceDeliveryProcess as $key => $row){
				$rowtotal = $row->total * $row->lookable->marketingOrderDelivery->marketingOrder->currency_rate;
				$rowtax = $row->tax * $row->lookable->marketingOrderDelivery->marketingOrder->currency_rate;
				$rowaftertax = $row->grandtotal * $row->lookable->marketingOrderDelivery->marketingOrder->currency_rate;
				$rowrounding = ((($row->total / $moi->total) * $moi->rounding) * $row->lookable->marketingOrderDelivery->marketingOrder->currency_rate);

				if($rowtotal > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coapenjualan->id,
						'account_id'	=> $coapenjualan->bp_journal ? $moi->account_id : NULL,
						'place_id'		=> $row->lookable->place_id,
						'warehouse_id'	=> $row->lookable->warehouse_id,
						'item_id'		=> $row->lookable->item_id,
						'type'			=> '2',
						'nominal'		=> $rowtotal,
					]);
				}

				if($rowtax > 0){
					$coa_sale_id = $row->lookable->marketingOrderDetail->taxId->coaSale;
				}

				if($rowrounding > 0 || $rowrounding < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
						'type'			=> $rowrounding > 0 ? '2' : '1',
						'nominal'		=> $rowrounding,
					]);
				}

				$total += $rowtotal;
				$tax += $rowtax;
				$total_after_tax += $rowaftertax;
				$rounding += $rowrounding;

				if(self::checkArrayRaw($arrNote,$row->lookable->marketingOrderDelivery->code) < 0){
					$arrNote[] = $row->lookable->marketingOrderDelivery->code;
				}

				if(self::checkArrayRaw($arrNote,$row->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code) < 0){
					$arrNote[] = $row->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;
				}
			}

			$grandtotal = ($total_after_tax + $rounding);

			foreach($moi->marketingOrderInvoiceDownPayment as $key => $row){
				$rowtotal = $row->total * $row->lookable->currency_rate;
				$rowtax = $row->tax * $row->lookable->currency_rate;

				if($rowtotal > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coauangmuka->id,
						'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $rowtotal,
					]);
				}

				$dp_total += $rowtotal;
				$dp_tax += $rowtax;

				CustomHelper::removeDeposit($row->lookable->account_id,$rowtotal + $rowtax);
			}

			$downpayment = $dp_total + $dp_tax;
			$tax -= $dp_tax;

			$balance = $grandtotal - $downpayment;
			
			if($balance > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapiutang->id,
					'account_id'	=> $coapiutang->bp_journal ? $moi->account_id : NULL,
					'type'			=> '1',
					'nominal'		=> $balance,
				]);
			}

			if($tax > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coa_sale_id->id,
					'account_id'	=> $coa_sale_id->bp_journal ? $moi->account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $tax,
					'note'			=> 'No Seri Pajak : '.$moi->tax_no,
				]);
			}

			CustomHelper::addCountLimitCredit($moi->account_id,$balance);

			$journal = Journal::find($query->id);
			$journal->note = $journal->note.' - '.implode(', ',$arrNote);
			$journal->save();

		}elseif($table_name == 'marketing_order_memos'){

			$mom = MarketingOrderMemo::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $mom->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			$coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$mom->company_id)->first();
			$coauangmuka = Coa::where('code','200.01.06.01.01')->where('company_id',$mom->company_id)->first();
			$coapotonganpenjualan = Coa::where('code','400.02.01.01.01')->where('company_id',$mom->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$mom->company_id)->first();
			$coahpp = Coa::where('code','500.01.01.01.01')->where('company_id',$mom->company_id)->first();

			$totalDebit = 0;
			$totalCredit = 0;
			$balance = 0;

			foreach($mom->marketingOrderMemoDetail as $row){
				if($row->lookable_type == 'marketing_order_invoice_details'){

					$total = round($row->total * $row->lookable->lookable->marketingOrderDelivery->marketingOrder->currency_rate,2);
					$tax = round($row->tax * $row->lookable->lookable->marketingOrderDelivery->marketingOrder->currency_rate,2);

					if($total > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coapotonganpenjualan->id,
							'account_id'	=> $coapotonganpenjualan->bp_journal ? $mom->account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $total,
						]);
						$totalDebit += $total;
					}
	
					if($tax > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->lookable->lookable->marketingOrderDetail->taxId->coa_sale_id,
							'account_id'	=> $row->lookable->lookable->marketingOrderDetail->taxId->coaSale->bp_journal ? $mom->account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $tax,
							'note'			=> 'No Seri Pajak : '.$mom->tax_no,
						]);
						$totalDebit += $tax;
					}

					if($row->grandtotal > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coapiutang->id,
							'account_id'	=> $coapiutang->bp_journal ? $mom->account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->grandtotal * $row->lookable->lookable->marketingOrderDelivery->marketingOrder->currency_rate,
						]);
						$totalCredit += $row->grandtotal * $row->lookable->lookable->marketingOrderDelivery->marketingOrder->currency_rate;
					}

					if($totalDebit !== $totalCredit){
						$balance = $totalDebit - $totalCredit;

						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coarounding->id,
							'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
							'type'			=> $balance > 0 ? '2' : '1',
							'nominal'		=> -1 * $balance,
						]);
					}

					if($mom->type == '2'){
						$hpp = $row->lookable->lookable->getPriceHpp() * $row->qty * $row->lookable->lookable->item->sell_convert;

						JournalDetail::create([
							'journal_id'	=> $query->id,
							'account_id'	=> $mom->account_id,
							'coa_id'		=> $row->lookable->lookable->itemStock->item->itemGroup->coa_id,
							'place_id'		=> $row->lookable->lookable->place_id,
							'item_id'		=> $row->lookable->lookable->item_id,
							'warehouse_id'	=> $row->lookable->lookable->warehouse_id,
							'type'			=> '1',
							'nominal'		=> $hpp,
						]);

						JournalDetail::create([
							'journal_id'	=> $query->id,
							'account_id'	=> $coahpp->bp_journal ? $mom->account_id : NULL,
							'coa_id'		=> $coahpp->id,
							'place_id'		=> $row->lookable->lookable->place_id,
							'item_id'		=> $row->lookable->lookable->item_id,
							'warehouse_id'	=> $row->lookable->lookable->warehouse_id,
							'type'			=> '2',
							'nominal'		=> $hpp,
						]);
	
						self::sendCogs($table_name,
							$mom->id,
							$row->lookable->lookable->place->company_id,
							$row->lookable->lookable->place_id,
							$row->lookable->lookable->warehouse_id,
							$row->lookable->lookable->item_id,
							$row->qty * $row->lookable->lookable->item->sell_convert,
							$hpp,
							'IN',
							$mom->post_date,
							$row->lookable->lookable->area_id,
							NULL,
						);
	
						self::sendStock(
							$row->lookable->lookable->place_id,
							$row->lookable->lookable->warehouse_id,
							$row->lookable->lookable->item_id,
							$row->qty * $row->lookable->lookable->item->sell_convert,
							'IN',
							$row->lookable->lookable->area_id,
							NULL
						);
					}
				}

				CustomHelper::removeCountLimitCredit($mom->account_id,$row->grandtotal);
			}

			if($mom->type == '3'){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapotonganpenjualan->id,
					'account_id'	=> $coapotonganpenjualan->bp_journal ? $mom->account_id : NULL,
					'type'			=> '1',
					'nominal'		=> $mom->total,
				]);

				if($mom->tax > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $mom->taxMaster->coa_sale_id,
						'account_id'	=> $mom->taxMaster->coaSale->bp_journal ? $mom->account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $mom->tax,
					]);
				}
	
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapiutang->id,
					'account_id'	=> $coapiutang->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $mom->grandtotal,
				]);

				CustomHelper::removeCountLimitCredit($mom->account_id,$mom->grandtotal);
			}
			
		}elseif($table_name == 'purchase_invoices'){
			self::removeJournal($table_name,$table_id);
			#start untuk po tipe biaya / jasa
			$totalOutSide = 0;

			$pi = PurchaseInvoice::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pi->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> isset($data->currency_id) ? $data->currency_id : NULL,
				'currency_rate'	=> isset($data->currency_rate) ? $data->currency_rate : NULL,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note,
				'status'		=> '3'
			]);

			$coauangmukapembelian = Coa::where('code','100.01.07.01.01')->where('company_id',$pi->company_id)->first();
			$coahutangbelumditagih = Coa::where('code','200.01.03.01.02')->where('company_id',$pi->company_id)->first();
			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pi->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$pi->company_id)->first();

			$grandtotal = 0;
			$tax = 0;
			$wtax = 0;
			$currency_rate = 1;

			$arrNote = [];

			$type = '';

			foreach($pi->purchaseInvoiceDetail as $row){
				
				if($row->lookable_type == 'coas'){

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->lookable_id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $row->lookable->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->total * $pi->currency_rate,
						'nominal_fc'	=> $row->total,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					$grandtotal += $row->grandtotal;
					$tax += $row->tax;
					$wtax += $row->wtax;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->tax * $pi->currency_rate,
							'nominal_fc'	=> $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->wtax * $pi->currency_rate,
							'nominal_fc'	=> $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->grandtotal * $pi->currency_rate,
						'nominal_fc'	=> $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

				}elseif($row->lookable_type == 'purchase_order_details'){
					$type = $pi->currency->type;
					$currency_rate = $pi->currency_rate;
					$pod = $row->lookable;

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $pod->coa_id,
						'place_id'		=> $pod->place_id,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $pod->coa->bp_journal ? $account_id : NULL,
						'department_id'	=> $pod->department_id,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $pod->getArrayTotal()['total'] * $pi->currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $pod->getArrayTotal()['total'] * $pi->currency_rate : $pod->getArrayTotal()['total'],
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					$grandtotal += $row->grandtotal * $pi->currency_rate;
					$tax += $row->tax * $pi->currency_rate;
					$wtax += $row->wtax * $pi->currency_rate;
					$currency_rate = $pi->currency_rate;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->tax * $pi->currency_rate,
							'nominal_fc'	=> $pi->currency->type == '1' ? $row->tax * $pi->currency_rate : $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->project_id ? $row->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->wtax * $pi->currency_rate,
							'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->wtax * $pi->currency_rate : $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->project_id ? $row->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->grandtotal * $pi->currency_rate,
						'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->grandtotal * $pi->currency_rate : $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					if(self::checkArrayRaw($arrNote,$pod->purchaseOrder->code) < 0){
						$arrNote[] = $pod->purchaseOrder->code;
					}

				}elseif($row->lookable_type == 'landed_cost_fee_details'){
					$type = $pi->currency->type;
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->lookable->landedCostFee->coa_id,
						'account_id'	=> $row->lookable->landedCostFee->coa->bp_journal ? $row->lookable->landedCost->account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->lookable->total * $pi->currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->lookable->total * $pi->currency_rate : $row->lookable->total,
						'note'			=> $row->lookable->landedCostFee->name,
					]);

					$grandtotal += $row->grandtotal * $pi->currency_rate;
					$tax += $row->tax * $pi->currency_rate;
					$wtax += $row->wtax * $pi->currency_rate;
					$currency_rate = $pi->currency_rate;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->tax * $pi->currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $pi->currency_rate : $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->wtax * $pi->currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->wtax * $pi->currency_rate : $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
						]);
					}
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->grandtotal * $row->lookable->landedCost->currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $pi->currency_rate : $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					if(self::checkArrayRaw($arrNote,$row->lookable->landedCost->code) < 0){
						$arrNote[] = $row->lookable->landedCost->code;
					}
				}else{
					$type = $pi->currency->type;
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangbelumditagih->id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $coahutangbelumditagih->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->lookable->purchaseOrderDetail->project_id ? $row->lookable->purchaseOrderDetail->project_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->total * $pi->currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->total * $pi->currency_rate : $row->total,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					$grandtotal += $row->grandtotal * $pi->currency_rate;
					$tax += $row->tax * $pi->currency_rate;
					$wtax += $row->wtax * $pi->currency_rate;
					$currency_rate = $pi->currency_rate;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->lookable->purchaseOrderDetail->project_id ? $row->lookable->purchaseOrderDetail->project_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->tax * $pi->currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $pi->currency_rate : $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'place_id'		=> $row->place_id ? $row->place_id : NULL,
							'line_id'		=> $row->line_id ? $row->line_id : NULL,
							'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'department_id'	=> $row->department_id ? $row->department_id : NULL,
							'project_id'	=> $row->lookable->purchaseOrderDetail->project_id ? $row->lookable->purchaseOrderDetail->project_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->wtax * $pi->currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '2' ? $row->wtax * $pi->currency_rate : $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->lookable->purchaseOrderDetail->project_id ? $row->lookable->purchaseOrderDetail->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->grandtotal * $pi->currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $pi->currency_rate : $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
					]);

					if(self::checkArrayRaw($arrNote,$row->lookable->goodReceipt->code) < 0){
						$arrNote[] = $row->lookable->goodReceipt->code;
					}
				}
			}

			#start journal rounding
			if($pi->rounding > 0 || $pi->rounding < 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coarounding->id,
					'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
					'type'			=> $pi->rounding > 0 ? '1' : '2',
					'nominal'		=> abs($pi->rounding * $currency_rate),
					'nominal_fc'	=> $type == '1' || $type == '' ? abs($pi->rounding * $currency_rate) : abs($pi->rounding),
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coahutangusaha->id,
					'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
					'type'			=> $pi->rounding > 0 ? '2' : '1',
					'nominal'		=> abs($pi->rounding * $currency_rate),
					'nominal_fc'	=> $type == '1' || $type == '' ? abs($pi->rounding * $currency_rate) : abs($pi->rounding),
				]);
			}

			#start journal down payment

			if($pi->downpayment > 0){
				$downpayment = 0;
				foreach($pi->purchaseInvoiceDp as $row){
					/* $downpayment += $row->nominal * $row->purchaseDownPayment->currency_rate; */
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->nominal * $pi->currency_rate,
						'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->nominal * $pi->currency_rate : $row->nominal,
					]);
	
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coauangmukapembelian->id,
						'account_id'	=> $coauangmukapembelian->bp_journal ? $account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->nominal * $pi->currency_rate,
						'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->nominal * $pi->currency_rate : $row->nominal,
					]);
				}
			}

			$pi->updateRootDocumentStatusDone();

		}elseif($table_name == 'marketing_order_down_payments'){

			$modp = MarketingOrderDownPayment::find($table_id);

			$coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$modp->company_id)->first();
			$coauangmuka = Coa::where('code','200.01.06.01.01')->where('company_id',$modp->company_id)->first();

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $modp->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> isset($data->currency_id) ? $data->currency_id : NULL,
				'currency_rate'	=> isset($data->currency_rate) ? $data->currency_rate : NULL,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coapiutang->id,
				'account_id'	=> $coapiutang->bp_journal ? $account_id : NULL,
				'type'			=> '1',
				'nominal'		=> $modp->grandtotal * $data->currency_rate,
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coauangmuka->id,
				'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
				'type'			=> '2',
				'nominal'		=> $modp->total * $data->currency_rate,
			]);

			if($modp->tax > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $modp->taxId->coa_sale_id,
					'account_id'	=> $modp->taxId->coaSale->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $modp->tax * $data->currency_rate,
				]);
			}

			CustomHelper::addCountLimitCredit($modp->account_id,$modp->grandtotal * $modp->currency_rate);

		}elseif($table_name == 'purchase_down_payments'){
			$pdp = PurchaseDownPayment::find($table_id);

			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pdp->company_id)->first();
			$coauangmuka = Coa::where('code','100.01.07.01.01')->where('company_id',$pdp->company_id)->first();

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pdp->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($pdp->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> isset($pdp->currency_id) ? $pdp->currency_id : NULL,
				'currency_rate'	=> isset($pdp->currency_rate) ? $pdp->currency_rate : NULL,
				'post_date'		=> $pdp->post_date,
				'note'			=> $pdp->note,
				'status'		=> '3'
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coauangmuka->id,
				'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
				'type'			=> '1',
				'nominal'		=> $pdp->grandtotal * $pdp->currency_rate,
				'nominal_fc'	=> $pdp->currency->type == '1' ? $pdp->grandtotal * $pdp->currency_rate : $pdp->grandtotal,
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coahutangusaha->id,
				'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
				'type'			=> '2',
				'nominal'		=> $pdp->grandtotal * $pdp->currency_rate,
				'nominal_fc'	=> $pdp->currency->type == '1' ? $pdp->grandtotal * $pdp->currency_rate : $pdp->grandtotal,
			]);

			CustomHelper::addDeposit($account_id,$pdp->grandtotal * $pdp->currency_rate);

		}elseif($table_name == 'employee_transfers'){
			$transfer = EmployeeTransfer::find($table_id);

			self::updateEmployeeTransfer($transfer);

		}elseif($table_name == 'production_issue_receives'){
			$pir = ProductionIssueReceive::find($table_id);
			
			$total = 0;

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pir->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			$target = $pir->productionIssueReceiveDetail()->where('type','2')->first();

			foreach($pir->productionIssueReceiveDetail()->where('type','2')->get() as $row){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $target->item->itemGroup->coa_id,
					'place_id'		=> $pir->productionOrder->productionSchedule->place_id,
					'line_id'		=> $pir->productionOrder->productionScheduleDetail->line_id,
					'item_id'		=> $row->lookable_id,
					'warehouse_id'	=> $pir->productionOrder->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $pir->productionOrder->total_product_cost,
				]);

				$shade = NULL;

				if($row->shading){
					$shading = ItemShading::where('item_id',$row->lookable_id)->where('code',$row->shading)->first();
					if(!$shading){
						$shade = ItemShading::create([
							'item_id'	=> $row->lookable_id,
							'code'		=> $row->shading,
						]);
					}else{
						$shade = $shading;
					}
				}

				self::sendCogs($table_name,
					$pir->id,
					$pir->company_id,
					$pir->productionOrder->productionSchedule->place_id,
					$pir->productionOrder->warehouse_id,
					$row->lookable_id,
					$row->qty * $row->item->production_convert,
					$pir->productionOrder->total_product_cost,
					'IN',
					$pir->post_date,
					$pir->productionOrder->area_id,
					NULL,
				);

				self::sendStock(
					$pir->productionOrder->productionSchedule->place_id,
					$pir->productionOrder->warehouse_id,
					$row->lookable_id,
					$row->qty * $row->item->production_convert,
					'IN',
					$pir->productionOrder->area_id,
					$shade ? $shade->id : NULL,
				);
			}

			foreach($pir->productionIssueReceiveDetail()->where('type','1')->get() as $row){
				if($row->lookable_type == 'items'){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->item->itemGroup->coa_id,
						'place_id'		=> $row->itemStock->place_id,
						'line_id'		=> $row->productionOrderDetail->productionOrder->productionScheduleDetail->line_id,
						'item_id'		=> $row->itemStock->item_id,
						'warehouse_id'	=> $row->itemStock->warehouse_id,
						'type'			=> '2',
						'nominal'		=> $row->total,
					]);
	
					self::sendCogs($table_name,
						$pir->id,
						$row->itemStock->place->company_id,
						$row->itemStock->place_id,
						$row->itemStock->warehouse_id,
						$row->itemStock->item_id,
						$row->qty * $row->item->production_convert,
						$row->total,
						'OUT',
						$pir->post_date,
						$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
						NULL,
					);
	
					self::sendStock(
						$row->itemStock->place_id,
						$row->itemStock->warehouse_id,
						$row->itemStock->item_id,
						$row->qty * $row->item->production_convert,
						'OUT',
						$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
						NULL,
					);
				}elseif($row->lookable_type == 'coas'){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->lookable_id,
						'line_id'		=> $row->productionOrderDetail->productionOrder->productionScheduleDetail->line_id,
						'type'			=> '2',
						'nominal'		=> $row->total,
					]);
				}
			}
		}elseif($table_name == 'closing_journals'){
			$cj = ClosingJournal::find($table_id);

			if($cj){

				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $cj->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'post_date'		=> $data->post_date,
					'note'			=> $cj->note,
					'status'		=> '3'
				]);
				
				foreach($cj->closingJournalDetail as $row){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->coa_id,
						'type'			=> $row->type,
						'nominal'		=> abs($row->nominal),
						'nominal_fc'	=> abs($row->nominal_fc),
					]);
				}

				$cj->update([
					'status'	=> '3'
				]);

				/* self::sendTrialBalance($cj->company_id, $cj->month, $cj); */
			}
		}elseif($table_name == 'purchase_orders'){
			$po = PurchaseOrder::find($table_id);

			if($po){
				$po->updateRootDocumentStatusDone();
			}
		}elseif($table_name == 'purchase_requests'){
			$pr = PurchaseRequest::find($table_id);

			if($pr){
				$pr->updateRootDocumentStatusDone();
			}
		}elseif($table_name == 'journals'){
			$je = Journal::find($table_id)->update(['status' => '3']);
		}
		/* else{

			$journalMap = MenuCoa::whereHas('menu', function($query) use ($table_name){
				$query->where('table_name',$table_name);
			})
			->whereHas('coa', function($query) use($data){
				$query->where('company_id',$data->company_id);
			})->get();

			if(count($journalMap) > 0){
				
				$arrdata = get_object_vars($data);

				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'code'			=> Journal::generateCode($data->post_date),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'currency_id'	=> isset($data->currency_id) ? $data->currency_id : NULL,
					'currency_rate'	=> isset($data->currency_rate) ? $data->currency_rate : NULL,
					'post_date'		=> $data->post_date,
					'note'			=> $data->code,
					'status'		=> '3'
				]);

				foreach($journalMap as $row){
					
					$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

					if($nominal > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
							'account_id'	=> $account_id,
							'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
							'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
							'type'			=> $row->type,
							'nominal'		=> $nominal
						]);
					}
				}
			}
		} */
	}

	public static function sendTrialBalance($company_id, $month, $closingJournal){
		$dt = strtotime($month);
		$nextmonth = date("Y-m", strtotime("+1 month", $dt));
		$journals = JournalDetail::whereHas('coa',function($query)use($company_id,$month){
			$query->where('company_id',$company_id)
				->whereRaw("SUBSTRING(code,1,1) IN ('1','2','3')");
		})->whereHas('journal',function($query)use($company_id,$month){
			$query->whereIn('status',['2','3'])
				->whereRaw("post_date LIKE '$month%'");
		})->get();

		$arr = [];
		foreach($journals as $row){
			$index = self::checkArray($arr,$row->coa_id);
			if($index < 0){
				$arr[] = [
					'coa_id'	=> $row->coa_id,
					'coa_code'	=> $row->coa->code,
					'balance'	=> $row->type == '1' ? $row->nominal : -1 * $row->nominal,
				];
			}else{
				if($row->type == '1'){
					$arr[$index]['balance'] += $row->nominal;
				}elseif($row->type == '2'){
					$arr[$index]['balance'] -= $row->nominal;
				}
			}
		}

		$collection = collect($arr)->sortBy('coa_code')->values()->all();

		if(count($collection) > 0){
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($nextmonth)).'00'),
				'lookable_type'	=> $closingJournal->getTable(),
				'lookable_id'	=> $closingJournal->id,
				'post_date'		=> $nextmonth.'-01',
				'note'			=> $closingJournal->note,
				'status'		=> '3'
			]);

			foreach($collection as $row){
				if($row['balance'] !== 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row['coa_id'],
						'type'			=> $row['balance'] >= 0 ? '1' : '2',
						'nominal'		=> abs($row['balance']),
						'nominal_fc'	=> 0,
						'note'			=> 'Saldo bulan '.date('F Y',strtotime($month)),
					]);
				}
			}
		}
	}

	public static function checkArray($arr,$val){
		$index = -1;
		foreach($arr as $key => $row){
			if($row['coa_id'] == $val){
				$index = $key;
			}
		}
		return $index;
	}

	public static function checkArrayRaw($arr,$val){
		$index = -1;
		foreach($arr as $key => $row){
			if($row == $val){
				$index = $key;
			}
		}
		return $index;
	}

	public static function removeJournal($table_name = null, $table_id = null){
		$data = Journal::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();

		foreach($data as $row){
			$row->journalDetail()->delete();
			$row->delete();
		}
	}

	public static function checkLockAcc($date = null){
		$month = date('Y-m',strtotime($date));
		$cekLock = LockPeriod::where('month',$month)->whereIn('status',['2','3'])->first();
		if($cekLock){
			$passedSpecial = false;

			if($cekLock->status_closing == '2'){
				foreach($cekLock->lockPeriodDetail as $row){
					if($row->user_id == session('bo_id')){
						$passedSpecial = true;
					}
				}
			}elseif($cekLock->status_closing == '1'){
				$passedSpecial = true;
			}

			if($passedSpecial){
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}

	public static function removeApproval($table_name = null, $table_id = null){
		$datasource = ApprovalSource::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();
		foreach($datasource as $row){
			foreach($row->approvalMatrix as $rowdetail){
				$rowdetail->delete();
			}
			$row->delete();
		}
	}

	public static function removeCogs($table_name = null, $table_id = null){
		$data = ItemCogs::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();

		if($data){
			foreach($data as $row){
				$item_id = $row->item_id;
				$place_id = $row->place_id;
				$warehouse_id = $row->warehouse_id;
				$area_id = $row->area_id ? $row->area_id : NULL;
				$item_shading_id = $row->item_shading_id ? $row->item_shading_id : NULL;
				$qty = $row->qty_in ? $row->qty_in : $row->qty_out;
				$type = $row->qty_in ? 'IN' : 'OUT';
				
				$row->delete();

				/* ResetCogs::dispatch($row->date,$place_id,$item_id);
				ResetStock::dispatch($place_id,$warehouse_id,$area_id,$item_id,$item_shading_id,$qty,$type); */
				self::resetStock($place_id,$warehouse_id,$area_id,$item_id,$item_shading_id,$qty,$type);
			}
		}
	}

	public static function resetStock($place_id,$warehouse_id,$area_id,$item_id,$shading,$qty,$type){
		$data = ItemStock::where('place_id',$place_id)->where('warehouse_id',$warehouse_id)->where('area_id',$area_id)->where('item_id',$item_id)->where('item_shading_id',$shading)->first();

		if($data){
			$data->update([
				'qty' => $type == 'IN' ? $data->qty - $qty : $data->qty + $qty,
			]);
		}else{
			ItemStock::create([
				'place_id'		    => $place_id,
				'warehouse_id'	    => $warehouse_id,
                'area_id'           => $area_id,
				'item_id'		    => $item_id,
                'item_shading_id'   => $shading,
				'qty'			    => $type == 'IN' ? 0 - $qty : $qty,
			]);
		}
	}

	public static function updateBalanceAsset($asset_id = null, $nominal = null, $type = null, $table = null){
		$asset = Asset::find($asset_id);
		
		if($asset){
			$asset->update([
				'accumulation_total'	=> $type == 'OUT' ? round($asset->accumulation_total + $nominal,3) : round($asset->accumulation_total - $nominal,3),
				'book_balance' 			=> $type == 'OUT' ? round($asset->book_balance - $nominal,3) : round($asset->book_balance + $nominal,3),
				'count_balance'			=> $type == 'OUT' ? ($table == 'retirements' ? 0 : ($asset->count_balance - 1)) : $asset->count_balance + 1,
			]);
		}
	}

	public static function removeStock($place_id = null, $warehouse_id = null, $item_id = null, $qty = null){
		$data = ItemStock::where('place_id',$place_id)->where('warehouse_id',$warehouse_id)->where('item_id',$item_id)->first();

		if($data){
			$data->update([
				'qty' => $data->qty - $qty,
			]);
		}
	}

	public static function terbilangWithKoma($angka){
		$arr = explode('.',strval(round($angka,2)));
		$angka=intval($arr[0]);
		$sen = '';
		if(count($arr) > 1){
			$sen = self::tkoma($arr[1]);
		}

		$terbilang = self::terbilang($angka).(count($arr) > 1 ? ' Koma '.$sen : '');

		return $terbilang;
	}

	public static function terbilang($angka) {
		$angka = strval($angka);
		
		$baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
	  
		$terbilang="";
		 if ($angka < 12){
			 $terbilang= " " . $baca[$angka];
		 }
		 else if ($angka < 20){
			 $terbilang= self::terbilangSen($angka - 10) . " belas";
		 }
		 else if ($angka < 100){
			 $terbilang= self::terbilangSen($angka / 10) . " puluh" . self::terbilangSen($angka % 10);
		 }
		 else if ($angka < 200){
			 $terbilang= " seratus" . self::terbilangSen($angka - 100);
		 }
		 else if ($angka < 1000){
			 $terbilang= self::terbilangSen($angka / 100) . " ratus" . self::terbilangSen($angka % 100);
		 }
		 else if ($angka < 2000){
			 $terbilang= " seribu" . self::terbilangSen($angka - 1000);
		 }
		 else if ($angka < 1000000){
			 $terbilang= self::terbilangSen($angka / 1000) . " ribu" . self::terbilangSen($angka % 1000);
		 }
		 else if ($angka < 1000000000){
			$terbilang= self::terbilangSen($angka / 1000000) . " juta" . self::terbilangSen($angka % 1000000);
		 }
		 else if ($angka < 1000000000000){
			$terbilang= self::terbilangSen($angka / 1000000000) . " miliar" . self::terbilangSen($angka % 1000000000);
		 }
		 
		 return ucwords($terbilang);
	 }

	public static function tkoma($angka){
		$baca =array("nol", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan");

		$temp = "";
		$pjg = strlen($angka);
		$pos = 0;

		while($pos < $pjg){
			$char =	 substr($angka,$pos,1);
			$pos++;
			$temp	.= " ".$baca[$char];
		}

		return ucwords($temp);
	}	

	 public static function terbilangSen($angka) {
		$angka=abs($angka);
		
		$baca =array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
	  
		$terbilang="";
		 if ($angka < 12){
			 $terbilang= " " . $baca[$angka];
		 }
		 else if ($angka < 20){
			 $terbilang= self::terbilangSen($angka - 10) . " belas";
		 }
		 else if ($angka < 100){
			 $terbilang= self::terbilangSen($angka / 10) . " puluh" . self::terbilangSen($angka % 10);
		 }
		 else if ($angka < 200){
			 $terbilang= " seratus" . self::terbilangSen($angka - 100);
		 }
		 else if ($angka < 1000){
			 $terbilang= self::terbilangSen($angka / 100) . " ratus" . self::terbilangSen($angka % 100);
		 }
		 else if ($angka < 2000){
			 $terbilang= " seribu" . self::terbilangSen($angka - 1000);
		 }
		 else if ($angka < 1000000){
			 $terbilang= self::terbilangSen($angka / 1000) . " ribu" . self::terbilangSen($angka % 1000);
		 }
		 else if ($angka < 1000000000){
			$terbilang= self::terbilangSen($angka / 1000000) . " juta" . self::terbilangSen($angka % 1000000);
		 }
		 else if ($angka < 1000000000000){
			$terbilang= self::terbilangSen($angka / 1000000000) . " miliar" . self::terbilangSen($angka % 1000000000);
		 }
		 
		 return ucwords($terbilang);
	 }

	 public static function hariIndo($hariInggris) {
		switch ($hariInggris) {
		  case 'Sunday':
			return 'Minggu';
		  case 'Monday':
			return 'Senin';
		  case 'Tuesday':
			return 'Selasa';
		  case 'Wednesday':
			return 'Rabu';
		  case 'Thursday':
			return 'Kamis';
		  case 'Friday':
			return 'Jumat';
		  case 'Saturday':
			return 'Sabtu';
		  default:
			return 'hari tidak valid';
		}
	  }

	 public static function tgl_indo($tanggal){
		$bulan = array (
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		$pecahkan = explode('-', $tanggal);
	 
		return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
	}

	public static function addDeposit($account_id = null, $nominal = null){
		$query = User::find($account_id);

		if($query->deposit){
			$query->deposit = $query->deposit + $nominal;
		}else{
			$query->deposit = 0 + $nominal;
		}

		$query->save();
	}

	public static function removeDeposit($account_id = null, $nominal = null){
		$query = User::find($account_id);

		if($query->deposit){
			$query->deposit = $query->deposit - $nominal;
		}else{
			$query->deposit = 0 - $nominal;
		}

		$query->save();
	}

	public static function addCountLimitCredit($account_id = null, $nominal = null){
		$query = User::find($account_id);
		$query->count_limit_credit = $query->count_limit_credit + $nominal;
		$query->save();
	}

	public static function removeCountLimitCredit($account_id = null, $nominal = null){
		$query = User::find($account_id);
		$query->count_limit_credit = $query->count_limit_credit - $nominal;
		$query->save();
	}

	public static function sendUsedData($table_name = null, $table_id = null, $ref = null){
		$count = UsedData::where('lookable_type',$table_name)->where('lookable_id',$table_id)->count();
		if($count == 0){
			$ud = UsedData::create([
				'user_id'		=> session('bo_id'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'ref'			=> $ref
			]);

			return $ud;
		}

		return '';
	}

	public static function removeUsedData($table_name = null, $table_id = null){
		UsedData::where('lookable_type',$table_name)->where('lookable_id',$table_id)->delete();
	}

	public static function checkUsedData($table_name = null, $table_id = null){
		$count = UsedData::where('lookable_type',$table_name)->where('lookable_id',$table_id)->count();

		if($count > 0){
			return true;
		}else{
			return false;
		}
	}

	public static function addNewItemService($item_id = null){
		$item = Item::find($item_id);

		if(str_contains($item,'-SVC')){
			$newItem = $item;
		}else{
			$cek = Item::where('code',$item->code.'-SVC')->where('status','1')->first();
			if($cek){
				$newItem = $cek;
			}else{
				$newItem = $item->replicate();
				$newItem->code = $item->code.'-SVC';
				$newItem->name = $item->name.' SERVICE';
				$newItem->save();
			}
		}

		return $newItem->id;
	}

	public static function formatConditionalQty($qty){
		$arr = explode('.',strval($qty));
		$value = 0;
		if(count($arr) > 1){
			if(floatval($arr[1]) > 0){
				$value = number_format(floatval($arr[0].'.'.$arr[1]),3,',','.');
			}else{
				$value = number_format(floatval($arr[0]),0,',','.');
			}
		}else{
			$value = number_format(floatval($arr[0]),0,',','.');
		}

		return $value;
	}

	public static function addNewPrinterCounter($table_name = null,$table_id = null){
		PrintCounter::create([
			'user_id'		=> session('bo_id'),
			'lookable_type'	=> $table_name,
			'lookable_id'	=> $table_id
		]);
	}
}