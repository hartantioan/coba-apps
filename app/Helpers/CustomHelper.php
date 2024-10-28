<?php

namespace App\Helpers;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ResetCogs;
use App\Jobs\ResetCogsNew;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use App\Jobs\ResetStock;
use App\Models\AdjustRate;
use App\Models\HistoryEmailDocument;
use App\Models\ApprovalMatrix;
use App\Models\LockPeriod;
use App\Models\ApprovalStage;
use App\Models\ApprovalSource;
use App\Models\OvertimeRequest;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateMenu;
use App\Models\PrintCounter;
use App\Models\Asset;
use App\Models\CancelDocument;
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
use App\Models\GoodScale;
use App\Models\Unit;
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
use App\Models\PurchaseOrderDetail;
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
use App\Models\MarketingOrder;
use App\Models\MaterialRequest;
use App\Models\ProductionBarcode;
use App\Models\ProductionBatch;
use App\Models\ProductionFgReceive;
use App\Models\ProductionHandover;
use App\Models\ProductionIssue;
use App\Models\ProductionRecalculate;
use App\Models\ProductionReceive;
use App\Models\ProductionRepack;
use App\Models\ProductionSchedule;
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

	public static function sendCogs($lookable_type = null, $lookable_id = null, $company_id = null, $place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $total = null, $type = null, $date = null, $area_id = null, $shading = null, $batch = null, $detail_type = null, $detail_id = null){
		/* ResetCogsHelper::gas($date,$company_id,$place_id,$item_id,$area_id,$shading,$batch);
		self::accumulateCogs($date,$company_id,$place_id,$item_id); */
		ResetCogsNew::dispatch($date,$company_id,$place_id,$item_id,$area_id,$shading,$batch, $detail_type = null, $detail_id = null);
	}

	public static function sendJournalWithOnlyCogs($table_name = null,$table_id = null,$account_id = null){

	}

	public static function sendStock($place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $type = null, $area_id = null, $shading = null, $batch = null){
		$old_data = ItemStock::where('place_id',$place_id)->where('item_id',$item_id)->where('warehouse_id',$warehouse_id)->where('area_id',$area_id)->where('item_shading_id',$shading)->where('production_batch_id',$batch)->first();
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
				'production_batch_id'=> $batch ?? NULL,
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

	public static function sendApprovalWithoutDelete($table_name,$table_id,$note){
		#delete approvalsource yang tidak punya matrix
		ApprovalSource::whereDoesntHave('lookable')->delete();

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

			if($table_name == 'production_schedules'){
				$ps = ProductionSchedule::find($table_id);
				$ps->productionScheduleDetail()->update([
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

	public static function sendApproval($table_name,$table_id,$note){
		#delete approvalsource yang tidak punya matrix
		/* ApprovalSource::whereDoesntHave('approvalMatrix')->forceDelete(); */

		ApprovalSource::whereDoesntHave('lookable')->delete();

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

		$underEbitda = false;

		if($table_name == 'marketing_orders'){
			$salesOrder = MarketingOrder::find($table_id);
			if($salesOrder){
				$underEbitda = $salesOrder->underEbitda();
			}
		}

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

			#if sales order disini ya
			/* if($table_name == 'marketing_orders'){
				if($underEbitda){
					$passed = true;
				}else{
					$passed = false;
				}
			} */

			if($passed == true){

				$count = 0;

				foreach($row->approvalTemplateStage()->orderBy('id')->get() as $rowTemplateStage){
					$status = $count == 0 ? '1': '0';
					$check = true;
					if($table_name == 'marketing_orders' && $rowTemplateStage->approvalStage->level == 2){
						if(!$underEbitda){
							$check = false;
						}
					}
					if($check){
						foreach($rowTemplateStage->approvalStage->approvalStageDetail as $rowStageDetail){
						
							ApprovalMatrix::create([
								'code'							=> strtoupper(Str::random(30)),
								'approval_template_stage_id'	=> $rowTemplateStage->id,
								'approval_source_id'			=> $source->id,
								'user_id'						=> $rowStageDetail->user_id,
								'date_request'					=> date('Y-m-d H:i:s'),
								'status'						=> $status
							]);
							if($rowStageDetail->user->phone == '085729547103' && $status == '1'){
								WaBlas::kirim_wa('085729547103','Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
								WaBlas::kirim_wa('081330074432','Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
							}
						}
						$count++;
					}
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

			if($table_name == 'production_schedules'){
				$ps = ProductionSchedule::find($table_id);
				$ps->productionScheduleDetail()->update([
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
			/* foreach($menu->menuUser as $row){
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
			} */

			if($to){
				/* if($adato == false){ */
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
				/* } */
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
				'status'		=> '3',
			]);

			$coa_credit = Coa::where('code','200.01.03.01.02')->where('company_id',$gr->company_id)->first();

			$currency_rate = 1;
			$currency_id = 1;

			foreach($gr->goodReceiptDetail as $rowdetail){

				$rowtotal = round($rowdetail->total * $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate,2);

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
					'lookable_type'	=> $gr->getTable(),
					'lookable_id'	=> $gr->id,
					'detailable_type'=> $rowdetail->getTable(),
					'detailable_id'	=> $rowdetail->id,
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
						'lookable_type'	=> $gr->getTable(),
						'lookable_id'	=> $gr->id,
						'detailable_type'=> $rowdetail->getTable(),
						'detailable_id'	=> $rowdetail->id,
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
					NULL,
					$rowdetail->getTable(),
					$rowdetail->id,
				);

				self::sendStock(
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					'IN',
					NULL,
					NULL,
					NULL,
				);

				$currency_rate = $rowdetail->purchaseOrderDetail->purchaseOrder->currency_rate;
				$currency_id = $rowdetail->purchaseOrderDetail->purchaseOrder->currency_id;
			}

			$gr->updateRootDocumentStatusDone();

			$query->update([
				'currency_id'	=> $currency_id,
				'currency_rate'	=> $currency_rate,
			]);

			if($gr->total <= 0){
				$gr->update([
					'status'	=> '3'
				]);
			}
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

		}elseif($table_name == 'good_scales'){
			$gs = GoodScale::find($table_id);

			if($gs){
				if($gs->type == '2' && $gs->goodScaleDetail()->exists() && $gs->qty_final > 0 && $gs->hasFrancoMod()){
					$place = Place::where('code',substr($gs->code,7,2))->where('status','1')->first();

					$query = Journal::create([
						'user_id'		=> session('bo_id'),
						'company_id'	=> $gs->company_id,
						'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $gs->id,
						'post_date'		=> $data->post_date,
						'note'			=> 'BIAYA KIRIM '.$gs->referenceGRPODO(),
						'status'		=> '3',
					]);

					$coabiayakirim = Coa::where('code','600.01.02.02.01')->where('company_id',$gs->company_id)->first();
					$coahutangusahabelumditagih = Coa::where('code','200.01.03.01.06')->where('company_id',$gs->company_id)->first();

					foreach($gs->goodScaleDetail as $row){
						if($row->lookable_type == 'marketing_order_deliveries'){
							if($row->lookable->type_delivery == '2' && $row->lookable->marketingOrderDeliveryProcess()->exists()){
								$delivery_cost = $row->total;
								if($delivery_cost > 0){
									JournalDetail::create([
										'journal_id'	=> $query->id,
										'account_id'	=> $coabiayakirim->bp_journal ? $gs->account_id : NULL,
										'coa_id'		=> $coabiayakirim->id,
										'place_id'      => $place->id,
										'type'			=> '1',
										'nominal'		=> $delivery_cost,
										'nominal_fc'    => $delivery_cost,
										'note'          => $row->lookable->code,
										'note2'			=> $gs->code,
										'lookable_type'	=> $gs->getTable(),
										'lookable_id'	=> $gs->id,
										'detailable_type'=> $row->getTable(),
										'detailable_id'	=> $row->id,
									]);

									JournalDetail::create([
										'journal_id'	=> $query->id,
										'account_id'	=> $coahutangusahabelumditagih->bp_journal ? $gs->account_id : NULL,
										'coa_id'		=> $coahutangusahabelumditagih->id,
										'place_id'      => $place->id,
										'type'			=> '2',
										'nominal'		=> $delivery_cost,
										'nominal_fc'    => $delivery_cost,
										'note'          => $row->lookable->code,
										'note2'			=> $gs->code,
										'lookable_type'	=> $gs->getTable(),
										'lookable_id'	=> $gs->id,
										'detailable_type'=> $row->getTable(),
										'detailable_id'	=> $row->id,
									]);
								}
							}
						}
					}

					$gs->createPurchaseOrder();
				}
				if($gs->type == '2'){
					$gs->update([
						'status'	=> '3',
					]);
				}
			}

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
						'lookable_type'	=> $ret->getTable(),
						'lookable_id'	=> $ret->id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
						'lookable_type'	=> $ret->getTable(),
						'lookable_id'	=> $ret->id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
						'lookable_type'	=> $ret->getTable(),
						'lookable_id'	=> $ret->id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $ret->getTable(),
							'lookable_id'	=> $ret->id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $ret->getTable(),
							'lookable_id'	=> $ret->id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
					'lookable_type'	=> $ret->getTable(),
					'lookable_id'	=> $ret->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
					]);
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $ip->coa_id,
					'account_id'	=> $ip->coa->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
					'type'			=> '1',
					'nominal'		=> floatval($ip->grandtotal * $ip->currency_rate),
					'nominal_fc'	=> $ip->currency->type == '1' ? floatval($ip->grandtotal * $ip->currency_rate) : floatval($ip->grandtotal),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);

				$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$ip->company_id)->first();
				$coareceivable = Coa::where('code','100.01.03.03.02')->where('company_id',$ip->company_id)->first();
				$coapiutangusaha = Coa::where('code','100.01.03.01.01')->where('company_id',$ip->company_id)->first();
				$coauangmuka = Coa::where('code','200.01.06.01.01')->where('company_id',$ip->company_id)->first();

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
									'lookable_type'					=> $table_name,
									'lookable_id'					=> $table_id,
									'detailable_type'				=> $row->getTable(),
									'detailable_id'					=> $row->id,
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
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);
							if($row->lookable->balance() <= 0){
								$row->lookable->update([
									'status'	=> '3',
								]);
							}
						}elseif($row->lookable_type == 'marketing_order_down_payments'){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coauangmuka->id,
								'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
								'type'			=> '2',
								'nominal'		=> $row->lookable->total * $row->lookable->currency_rate,
								'nominal_fc'	=> $row->lookable->total,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);

							if($row->lookable->tax > 0){
								JournalDetail::create([
									'journal_id'	=> $query->id,
									'coa_id'		=> $row->lookable->taxId->coa_sale_id,
									'account_id'	=> $row->lookable->taxId->coaSale->bp_journal ? $account_id : NULL,
									'type'			=> '2',
									'nominal'		=> $row->lookable->tax * $data->currency_rate,
									'nominal_fc'	=> $row->lookable->tax,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
									'note'			=> $row->lookable->tax_no,
								]);
							}
							self::addDeposit($row->lookable->account_id,floatval($row->lookable->total * $ip->currency_rate));
							$row->lookable->update([
								'status'	=> '3'
							]);
						}else{
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coapiutangusaha->id,
								'account_id'	=> $coapiutangusaha->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
								'type'			=> '2',
								'nominal'		=> floatval($row->subtotal * $ip->currency_rate),
								'nominal_fc'	=> $ip->currency->type == '1' ? floatval($row->subtotal * $ip->currency_rate) : floatval($row->subtotal),
								'note'			=> $row->note,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);
							if($row->lookable_type == 'marketing_order_invoices'){
								if($row->lookable->balancePaymentIncoming() <= 0){
									$row->lookable->update([
										'status'	=> '3'
									]);
								}
							}
						}
						CustomHelper::removeCountLimitCredit($row->lookable->account_id,floatval($row->subtotal * $ip->currency_rate));
						if(self::checkArrayRaw($arrNote,$row->lookable->code) < 0){
							$arrNote[] = $row->lookable->code;
						}
					}else{

					}
				}

				if($ip->rounding > 0 || $ip->rounding < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? ($ip->account_id ? $ip->account_id : NULL) : NULL,
						'type'			=> $ip->rounding > 0 ? '2' : '1',
						'nominal'		=> floatval(abs($ip->rounding * $ip->currency_rate)),
						'nominal_fc'	=> $ip->currency->type == '1' ? floatval(abs($ip->rounding * $ip->currency_rate)) : floatval(abs($ip->rounding)),
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
					]);
				}

				if($ip->listBgCheck()->exists()){
					$ip->listBgCheck->update([
						'status'		=> '3',
						'pay_date'		=> $ip->post_date,
						'grandtotal'	=> $ip->grandtotal,
					]);
				}
			}

			$ip->update([
				'status'	=> '3'
			]);

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

				foreach($pr->paymentRequestCost as $row){
					if($row->nominal_debit_fc > 0 || $row->nominal_debit_fc < 0){
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
									'account_id'                    => $row->coa->bp_journal ? $pr->account_id : NULL,
									'department_id'                 => $rowcost->department_id ? $rowcost->department_id : $row->division_id,
									'project_id'					=> $row->project_id ? $row->project_id : NULL,
									'type'                          => '1',
									'nominal'                       => floatval($nominal),
									'nominal_fc'					=> floatval($nominal),
									'note'							=> $row->note,
									'note2'							=> $row->note2,
									'lookable_type'					=> $table_name,
									'lookable_id'					=> $table_id,
									'detailable_type'				=> $row->getTable(),
									'detailable_id'					=> $row->id,
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
								'department_id'	=> $row->department_id ? $row->department_id : NULL,
								'project_id'	=> $row->project_id ? $row->project_id : NULL,
								'type'			=> '1',
								'nominal'		=> floatval($row->nominal_debit_fc * $pr->currency_rate),
								'nominal_fc'	=> floatval($row->nominal_debit_fc),
								'note'			=> $row->note,
								'note2'			=> $row->note2,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);
						}
					}

					if($row->nominal_credit_fc > 0 || $row->nominal_credit_fc < 0){
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
									'account_id'                    => $row->coa->bp_journal ? $pr->account_id : NULL,
									'department_id'                 => $rowcost->department_id ? $rowcost->department_id : $row->division_id,
									'project_id'					=> $row->project_id ? $row->project_id : NULL,
									'type'                          => '2',
									'nominal'                       => $nominal,
									'nominal_fc'					=> $nominal,
									'note'							=> $row->note,
									'note2'							=> $row->note2,
									'lookable_type'					=> $table_name,
									'lookable_id'					=> $table_id,
									'detailable_type'				=> $row->getTable(),
									'detailable_id'					=> $row->id,
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
								'department_id'	=> $row->department_id ? $row->department_id : NULL,
								'project_id'	=> $row->project_id ? $row->project_id : NULL,
								'type'			=> '2',
								'nominal'		=> floatval($row->nominal_credit_fc),
								'nominal_fc'	=> floatval($row->nominal_credit_fc),
								'note'			=> $row->note,
								'note2'			=> $row->note2,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);
						}
					}
				}

				foreach($pr->paymentRequestDetail as $row){
					if($row->lookable_type !== 'fund_requests'){
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
									'lookable_type'					=> $table_name,
									'lookable_id'					=> $table_id,
									'detailable_type'				=> $row->getTable(),
									'detailable_id'					=> $row->id,
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
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);
						}
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
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
						'lookable_type'					=> $table_name,
						'lookable_id'					=> $table_id,
						'detailable_type'				=> $row->getTable(),
						'detailable_id'					=> $row->id,
					]);
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
						$balanceReal = round($row->nominal * $row->lookable->latestCurrencyRateByDate($op->pay_date),3);
						if($row->lookable->getTotalPaid() <= 0){
							$row->lookable->update([
								'status'	=> '3'
							]);
							foreach($row->lookable->purchaseInvoiceDetail as $rowinvoicedetail){
								if($rowinvoicedetail->fundRequestDetail()->exists()){
									if(!$rowinvoicedetail->fundRequestDetail->fundRequest->hasBalanceInvoice()){
										$rowinvoicedetail->fundRequestDetail->fundRequest->update([
											'status'			=> '3',
											'balance_status'	=> '1'
										]);
									}
								}
							}
						}
					}elseif($row->lookable_type == 'fund_requests'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
						if($row->lookable->document_status == '2'){
							$row->lookable->update([
								'balance_status'	=> '1'
							]);
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
						$balanceReal = round($row->lookable->balancePaidExcept($row->id) * $row->lookable->latestCurrencyRateByDate($op->pay_date),2);
						if($row->lookable->getTotalPaid() <= 0){
							$row->lookable->update([
								'status'	=> '3'
							]);
							foreach($row->lookable->purchaseDownPaymentDetail as $rowdpdetail){
								if($rowdpdetail->fundRequestDetail()->exists()){
									$rowdpdetail->fundRequestDetail->fundRequest->update([
										'status'	=> '3'
									]);
								}
							}
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
								'note'							=> $op->paymentRequest->code,
								'note2'							=> $row->lookable_type == 'fund_requests' ? $row->lookable->code : '',
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
							'note'			=> $op->paymentRequest->code,
							'note2'			=> $row->lookable_type == 'fund_requests' ? $row->lookable->code : '',
						]);
						if($row->lookable_type == 'marketing_order_memos'){
							CustomHelper::addCountLimitCredit($op->account_id,$balanceReal);
						}
					}
				}else{
					if($row->lookable_type == 'fund_requests'){
						$mustpay = $row->nominal;
						$balanceReal = $row->nominal * $op->currency_rate;
						if($row->lookable->document_status == '2'){
							$row->lookable->update([
								'balance_status'	=> '1'
							]);
						}
						if($row->lookable->account->type == '1' && $row->lookable->type == '1'){
							CustomHelper::removeCountLimitCredit($row->lookable->account_id,$mustpay);
						}
					}
				}

				if(in_array($row->lookable_type,['purchase_invoices','purchase_down_payments','fund_requests'])){
					CustomHelper::updateStatus($row->lookable_type,$row->lookable_id,'3');
				}

				$totalMustPay += $mustpay;
				$totalReal += $balanceReal;
			}

			foreach($op->paymentRequest->paymentRequestCost as $row){
				if($row->nominal_debit_fc > 0 || $row->nominal_debit_fc < 0){
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
					}
				}

				if($row->nominal_credit_fc > 0 || $row->nominal_credit_fc < 0){
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
					}
				}
			}

			if($op->rounding > 0 || $op->rounding < 0){
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
					]);
				}
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
						'type'			=> $balanceKurs < 0  ? '1' : '2',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
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
							'lookable_type'					=> $table_name,
							'lookable_id'					=> $table_id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
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
					'lookable_type'					=> $table_name,
					'lookable_id'					=> $table_id,
					'detailable_type'				=> $row->getTable(),
					'detailable_id'					=> $row->id,
				]);
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $op->coa_source_id,
				'account_id'	=> $op->coaSource->bp_journal ? $op->account_id : NULL,
				'type'			=> '2',
				'nominal'		=> floatval($totalPay),
				'nominal_fc'	=> $op->currency->type == '1' ? floatval($totalPay) : floatval($totalPay / $op->currency_rate),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
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
				'note'			=> $gr->note,
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
					'nominal'		=> round($row->total * $gr->currency_rate,2),
					'nominal_fc'	=> $gr->currency->type == '1' ? round($row->total * $gr->currency_rate,2) : $row->total,
					'item_id'		=> $row->item_id,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
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
							'lookable_type'					=> $table_name,
							'lookable_id'					=> $table_id,
							'detailable_type'				=> $row->getTable(),
							'detailable_id'					=> $row->id,
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
						'nominal'		=> round($row->total * $gr->currency_rate,2),
						'nominal_fc'	=> $gr->currency->type == '1' ? round($row->total * $gr->currency_rate,2) : $row->total,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);
				}

				self::sendCogs('good_receives',
					$gr->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					round($row->total * $gr->currency_rate,2),
					'IN',
					$gr->post_date,
					$row->area_id,
					$row->item_shading_id ? $row->item_shading_id : NULL,
					$row->productionBatch()->exists() ? $row->productionBatch->id : ($row->itemStock()->exists() ? $row->itemStock->production_batch_id : NULL),
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					'IN',
					$row->area_id ? $row->area_id : NULL,
					$row->item_shading_id ? $row->item_shading_id : NULL,
					$row->productionBatch()->exists() ? $row->productionBatch->id : ($row->itemStock()->exists() ? $row->itemStock->production_batch_id : NULL),
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
					NULL,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty * $row->item->sell_convert,
					'IN',
					NULL,
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

			$currency_id = 1;
			$currency_rate = 1;
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
						'note'			=> $row->note,
						'note2'			=> $row->note2,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
					'note'			=> $row->note,
					'note2'			=> $row->note2,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
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
					NULL,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->goodReceiptDetail->place_id,
					$row->goodReceiptDetail->warehouse_id,
					$row->item_id,
					$row->qtyConvert(),
					'OUT',
					NULL,
					NULL,
					NULL,
				);

				$row->goodReceiptDetail->goodReceipt->updateRootDocumentStatusProcess();

				$currency_id = $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_id;
				$currency_rate = $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_rate;
			}

			$query->update([
				'status'		=> '3',
				'currency_rate'	=> $currency_rate,
				'currency_id'	=> $currency_id,
			]);

		}elseif($table_name == 'marketing_order_delivery_processes'){

			$sj = MarketingOrderDeliveryProcess::find($table_id);

			if($sj){
				if($sj->driver_hp){
					WaBlas::kirim_wa($sj->driver_hp,'Dokumen Surat Jalan '.$sj->code.' sudah bisa diupdate oleh driver. Silahkan klik link : '.env('APP_URL').'/admin/sales/delivery_order/driver/'.CustomHelper::encrypt($sj->code).'?d='.CustomHelper::encrypt($sj->driver_name).'&p='.CustomHelper::encrypt($sj->driver_hp));
					WaBlas::kirim_wa('081330074432','Dokumen Surat Jalan '.$sj->code.' sudah bisa diupdate oleh driver. Silahkan klik link : '.env('APP_URL').'/admin/sales/delivery_order/driver/'.CustomHelper::encrypt($sj->code).'?d='.CustomHelper::encrypt($sj->driver_name).'&p='.CustomHelper::encrypt($sj->driver_hp));
				}
				$sj->marketingOrderDelivery->update([
					'status'	=> '3'
				]);

				$weight_netto = 0;
				if($sj->marketingOrderDelivery->marketingOrderDeliveryRemapParent()->exists()){
					$gs = $sj->marketingOrderDelivery->goodScaleDetail->goodScale;
					$totalProportional = 0;
					$arr_delivery_no = [];
					foreach($gs->goodScaleDetail as $row){
						if($row->lookable_type == 'marketing_order_deliveries'){
							if($row->lookable->marketingOrderDeliveryProcess()->exists()){
								$totalProportional += $row->lookable->marketingOrderDeliveryProcess->totalQty();
								$arr_delivery_no[] = $row->lookable->marketingOrderDeliveryProcess->code;
							}
						}
					}
					$gs->update([
						'delivery_no'	=> implode(',',$arr_delivery_no),
					]);
					if($totalProportional > 0){
						foreach($gs->goodScaleDetail as $row){
							if($row->lookable_type == 'marketing_order_deliveries'){
								if($row->lookable->marketingOrderDeliveryProcess()->exists()){
									$bobot = round($row->lookable->marketingOrderDeliveryProcess->totalQty() / $totalProportional,3);
									$qty = round($gs->qty_final * $bobot,3);
									$total = $row->lookable->marketingOrderDeliveryProcess->deliveryCost($qty);
									$row->lookable->marketingOrderDeliveryProcess->update([
										'weight_netto' => $qty,
									]);
									$row->update([
										'qty'   => $qty,
										'total' => $total,
									]);
								}
							}
						}
						CustomHelper::removeJournal($gs->getTable(),$gs->id);
						CustomHelper::sendJournal($gs->getTable(),$gs->id,$gs->account_id);
					}
				}
			}

		}elseif($table_name == 'marketing_order_deliveries'){
			$mod = MarketingOrderDelivery::find($table_id);
			if($mod){
				if($mod->status == '2' && $mod->user_update_id){
					foreach($mod->marketingOrderDeliveryDetail as $row){
						if(!$row->marketingOrderDetail->marketingOrder->hasBalanceMod()){
							$row->marketingOrderDetail->marketingOrder->update([
								'status'	=> '3'
							]);
						}
					}
				}
			}
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
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
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
							'lookable_type'					=> $table_name,
							'lookable_id'					=> $table_id,
							'detailable_type'				=> $row->getTable(),
							'detailable_id'					=> $row->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
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
					$row->itemStock->production_batch_id ? $row->itemStock->production_batch_id : NULL,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->itemStock->item_id,
					$row->qty,
					'OUT',
					$row->itemStock->area_id ? $row->itemStock->area_id : NULL,
					$row->itemStock->item_shading_id ? $row->itemStock->item_shading_id : NULL,
					$row->itemStock->production_batch_id ? $row->itemStock->production_batch_id : NULL,
				);
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
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
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
							'lookable_type'					=> $table_name,
							'lookable_id'					=> $table_id,
							'detailable_type'				=> $row->getTable(),
							'detailable_id'					=> $row->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
					$row->goodIssueDetail->itemStock->production_batch_id ? $row->goodIssueDetail->itemStock->production_batch_id : NULL,
					$row->getTable(),
					$row->id
				);

				self::sendStock(
					$row->goodIssueDetail->itemStock->place_id,
					$row->goodIssueDetail->itemStock->warehouse_id,
					$row->item_id,
					$row->qty,
					'IN',
					$row->goodIssueDetail->itemStock->area_id ? $row->goodIssueDetail->itemStock->area_id : NULL,
					$row->goodIssueDetail->itemStock->item_shading_id ? $row->goodIssueDetail->itemStock->item_shading_id : NULL,$row->goodIssueDetail->itemStock->production_batch_id ? $row->goodIssueDetail->itemStock->production_batch_id : NULL,
				);
			}

			$gr->update([
				'status' => '3'
			]);

		}elseif($table_name == 'landed_costs'){

			$lc = LandedCost::find($data->id);

			if($lc){
				$arrNote = [];

				$otherLc = NULL;

				$coaselisihhargabahan = Coa::where('code','500.02.01.13.01')->where('company_id',$lc->company_id)->where('status','1')->first();

				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $lc->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> 'landed_costs',
					'lookable_id'	=> $lc->id,
					'post_date'		=> $data->post_date,
					'note'			=> $data->note,
					'status'		=> '3',
					'currency_id'	=> $lc->currency_id,
					'currency_rate'	=> $lc->currency_rate,
				]);

				$totalitem = 0;
				$totalcost = 0;
				$totalfcitem = 0;
				$totalfccost = 0;

				foreach($lc->landedCostDetail as $rowdetail){
					$rowfc = $rowdetail->nominal;
					if($rowdetail->lookable_type == 'landed_cost_details'){
						$otherLc = $rowdetail->lookable->landedCost;
						$rowfc = round($rowdetail->nominal - $rowdetail->lookable->nominal,2);
						$rowtotal = round($rowdetail->nominal * $lc->currency_rate,2) - round($rowdetail->lookable->nominal * $rowdetail->lookable->landedCost->currency_rate,2);
					}else{
						$rowtotal = round($rowdetail->nominal * $lc->currency_rate,2);
						$rowdetail->lookable->goodReceipt->update([
							'status_lc' 		=> '2',
							'is_multiple_lc'	=> NULL,
						]);
					}
					$totalitem += $rowtotal;
					$totalfcitem += $rowfc;

					$itemdata = ItemCogs::where('place_id',$rowdetail->place_id)->where('item_id',$rowdetail->item_id)->orderByDesc('date')->orderByDesc('id')->first();
					if($itemdata){
						if($itemdata->qty_final > 0){
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
								'nominal'		=> $rowtotal,
								'nominal_fc'	=> $rowfc,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $rowdetail->getTable(),
								'detailable_id'	=> $rowdetail->id,
							]);

							self::sendCogs('landed_costs',
								$lc->id,
								$rowdetail->place->company_id,
								$rowdetail->place_id,
								$rowdetail->warehouse_id,
								$rowdetail->item_id,
								0,
								$rowtotal,
								'IN',
								$lc->post_date,
								NULL,
								NULL,
								NULL,
								$rowdetail->getTable(),
								$rowdetail->id,
							);
						}else{
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coaselisihhargabahan->id,
								'place_id'		=> $rowdetail->place_id,
								'line_id'		=> $rowdetail->line_id ? $rowdetail->line_id : NULL,
								'machine_id'	=> $rowdetail->machine_id ? $rowdetail->machine_id : NULL,
								'account_id'	=> $coaselisihhargabahan->bp_journal ? $lc->account_id : NULL,
								'department_id'	=> $rowdetail->department_id ? $rowdetail->department_id : NULL,
								'warehouse_id'	=> $rowdetail->warehouse_id,
								'item_id'		=> $rowdetail->item_id,
								'type'			=> '1',
								'nominal'		=> $rowtotal,
								'nominal_fc'	=> $rowfc,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $rowdetail->getTable(),
								'detailable_id'	=> $rowdetail->id,
							]);
						}
					}
				}

				if($otherLc){
					foreach($otherLc->landedCostFeeDetail as $rowfee){
						$dataother = $lc->landedCostFeeDetail()->where('landed_cost_fee_id',$rowfee->landed_cost_fee_id)->first();
						if($dataother){
							$rowfc = round($dataother->total - $rowfee->total,2);
							$rowtotal = round($dataother->total * $lc->currency_rate,2) - round($rowfee->total * $rowfee->landedCost->currency_rate,2);
							$totalcost += $rowtotal;
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $dataother->landedCostFee->coa_id,
								'account_id'	=> $dataother->landedCostFee->coa->bp_journal ? $lc->account_id : NULL,
								'type'			=> '2',
								'nominal'		=> $rowtotal,
								'nominal_fc'	=> $rowfc,
								'note'			=> $dataother->landedCostFee->name,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $rowfee->getTable(),
								'detailable_id'	=> $rowfee->id,
							]);
							$totalfccost += $rowfc;
						}
					}
				}else{
					foreach($lc->landedCostFeeDetail as $rowdetail){
						$totalcost += round($rowdetail->total * $lc->currency_rate,2);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $rowdetail->landedCostFee->coa_id,
							'account_id'	=> $rowdetail->landedCostFee->coa->bp_journal ? $lc->account_id : NULL,
							'type'			=> '2',
							'nominal'		=> round($rowdetail->total * $lc->currency_rate,2),
							'nominal_fc'	=> $lc->currency->type == '1' ? $rowdetail->total * $lc->currency_rate : $rowdetail->total,
							'note'			=> $rowdetail->landedCostFee->name,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowdetail->getTable(),
							'detailable_id'	=> $rowdetail->id,
						]);
						$totalfccost += $rowdetail->total;
					}
				}

				$balance = round($totalitem - $totalcost,2);
				$balancefc = round($totalfcitem - $totalfccost,2);
				if($balance < 0 || $balance > 0){
					$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$lc->company_id)->first();
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
						'type'			=> $balance < 0 ? '1' : '2',
						'nominal'		=> abs($balance),
						'nominal_fc'	=> abs($balancefc),
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowdetail->getTable(),
							'detailable_id'	=> $rowdetail->id,
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
							'nominal_fc'	=> -1 * $rowdetail->nominal,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowdetail->getTable(),
							'detailable_id'	=> $rowdetail->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowdetail->getTable(),
							'detailable_id'	=> $rowdetail->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowdetail->getTable(),
							'detailable_id'	=> $rowdetail->id,
						]);
					}
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
						$rowdetail->itemStock->productionBatch()->exists() ? $rowdetail->itemStock->production_batch_id : NULL,
						$rowdetail->getTable(),
						$rowdetail->id,
					);
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
					'status'		=> '3',
					'currency_rate'	=> 1,
					'currency_id'	=> 1,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $rowdetail->getTable(),
						'detailable_id'	=> $rowdetail->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $rowdetail->getTable(),
						'detailable_id'	=> $rowdetail->id,
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
					$rowdetail->itemStock->production_batch_id,
					$rowdetail->getTable(),
					$rowdetail->id,
				);

				self::sendStock(
					$rowdetail->itemStock->place_id,
					$rowdetail->itemStock->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					'OUT',
					$rowdetail->itemStock->area_id,
					$rowdetail->itemStock->item_shading_id,
					$rowdetail->itemStock->production_batch_id,
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
					'status'		=> '3',
					'currency_rate'	=> 1,
					'currency_id'	=> 1,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $rowdetail->getTable(),
						'detailable_id'	=> $rowdetail->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $rowdetail->getTable(),
						'detailable_id'	=> $rowdetail->id,
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
					$rowdetail->itemStock->production_batch_id,
					$rowdetail->getTable(),
					$rowdetail->id,
				);

				self::sendStock(
					$iti->inventoryTransferOut->place_to,
					$iti->inventoryTransferOut->warehouse_to,
					$rowdetail->item_id,
					$rowdetail->qty,
					'IN',
					$rowdetail->area_id,
					$rowdetail->itemStock->item_shading_id,
					$rowdetail->itemStock->production_batch_id,
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
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			foreach($dpr->depreciationDetail as $row){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->cost_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '1',
					'nominal'		=> $row->nominal,
					'nominal_fc'	=> $row->nominal,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->depreciation_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '2',
					'nominal'		=> $row->nominal,
					'nominal_fc'	=> $row->nominal,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
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
							'note2'			=> date('d/m/Y',strtotime($pm->return_date)),
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
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
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
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
								NULL,
								$row->getTable(),
								$row->id,
							);

							self::sendStock(
								$row->lookable->lookable->place_id,
								$row->lookable->lookable->warehouse_id,
								$row->lookable->lookable->item_id,
								round($row->qty * $row->lookable->lookable->qty_conversion,3),
								'OUT',
								NULL,
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
							'note2'			=> date('d/m/Y',strtotime($pm->return_date)),
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'note2'			=> date('d/m/Y',strtotime($pm->return_date)),
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'note2'			=> date('d/m/Y',strtotime($pm->return_date)),
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coarounding->id,
					'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $pm->rounding > 0 ? $pm->rounding : $pm->rounding,
					'nominal_fc'	=> $type == '1' || $type == '' ? ($pm->rounding > 0 ? $pm->rounding : $pm->rounding) : $pm->rounding,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
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
				$account_id = $row->outgoingPayment()->exists() ? $row->outgoingPayment->account_id : $row->personalCloseBill->user_id;
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapiutangbs->id,
					'account_id'	=> $coapiutangbs->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $row->nominal * $cb->currency_rate,
					'nominal_fc'	=> $cb->currency->type == '1' || $cb->currency->type == '' ? $row->nominal * $cb->currency_rate : $row->nominal,
					'note'			=> $row->note,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);

				if($row->outgoingPayment()->exists()){
					if($row->outgoingPayment->balancePaymentCross() <= 0){
						foreach($row->outgoingPayment->paymentRequest->paymentRequestDetail as $rowdetail){
							if($rowdetail->lookable_type == 'fund_requests'){
								$rowdetail->lookable->update([
									'balance_status'	=> '1'
								]);
							}
						}
					}
				}
				if($row->personalCloseBill()->exists()){
					foreach($row->personalCloseBill->personalCloseBillDetail as $rowdetail){
						$rowdetail->fundRequest->update([
							'balance_status'	=> '1',
						]);
						/* CustomHelper::removeCountLimitCredit($rowdetail->fundRequest->account_id,$rowdetail->nominal * $cb->currency_rate); */
					}
					$row->personalCloseBill->update([
						'status'	=> '3'
					]);
				}
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
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
								'lookable_type'					=> $table_name,
								'lookable_id'					=> $table_id,
								'detailable_type'				=> $row->getTable(),
								'detailable_id'					=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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

			$place = Place::where('code',substr($moi->code,7,2))->where('status','1')->first();

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coapenjualan->id,
				'account_id'	=> $coapenjualan->bp_journal ? $moi->account_id : NULL,
				'place_id'		=> $place->id,
				'type'			=> '2',
				'nominal'		=> $moi->subtotal,
				'nominal_fc'	=> $moi->subtotal,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			if($moi->downpayment > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coauangmuka->id,
					'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
					'place_id'		=> $place->id,
					'type'			=> '1',
					'nominal'		=> $moi->downpayment,
					'nominal_fc'	=> $moi->downpayment,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
				CustomHelper::removeDeposit($moi->account_id,$moi->downpayment);
			}

			if($moi->tax > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $moi->taxMaster->coa_sale_id,
					'account_id'	=> $moi->taxMaster->coaSale->bp_journal ? $moi->account_id : NULL,
					'place_id'		=> $place->id,
					'type'			=> '2',
					'nominal'		=> $moi->tax,
					'nominal_fc'	=> $moi->tax,
					'note'			=> 'No Seri Pajak : '.$moi->tax_no,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			if($moi->grandtotal > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coapiutang->id,
					'account_id'	=> $coapiutang->bp_journal ? $moi->account_id : NULL,
					'place_id'		=> $place->id,
					'type'			=> '1',
					'nominal'		=> $moi->grandtotal,
					'nominal_fc'	=> $moi->grandtotal,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			CustomHelper::addCountLimitCredit($moi->account_id,$moi->grandtotal);

			/* $total = 0;
			$tax = 0;
			$total_after_tax = 0;
			$rounding = 0;
			$grandtotal = 0;
			$downpayment = 0;
			$balance = 0;
			$dp_total = 0;
			$dp_tax = 0;
			$coa_sale_id = null;

			foreach($moi->marketingOrderInvoiceDeliveryProcessDetail as $key => $row){
				$rowtotal = $row->total * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->currency_rate;
				$rowtax = $row->tax * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->currency_rate;
				$rowaftertax = $row->grandtotal * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->currency_rate;
				$rowrounding = ((($row->total / $moi->total) * $moi->rounding) * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->currency_rate);

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
						'nominal_fc'	=> $rowtotal,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);
				}

				if($rowtax > 0){
					$coa_sale_id = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->taxId->coaSale;
				}

				if($rowrounding > 0 || $rowrounding < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
						'type'			=> $rowrounding > 0 ? '2' : '1',
						'nominal'		=> $rowrounding,
						'nominal_fc'	=> $rowrounding,
						'lookable_type' => $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);
				}

				$total += $rowtotal;
				$tax += $rowtax;
				$total_after_tax += $rowaftertax;
				$rounding += $rowrounding;

				if(self::checkArrayRaw($arrNote,$row->lookable->marketingOrderDeliveryDetail->marketingOrderDelivery->code) < 0){
					$arrNote[] = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDelivery->code;
				}

				if(self::checkArrayRaw($arrNote,$row->lookable->marketingOrderDeliveryProcess->code) < 0){
					$arrNote[] = $row->lookable->marketingOrderDeliveryProcess->code;
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
						'nominal_fc'	=> $rowtotal,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
					'nominal_fc'	=> $balance,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			if($tax > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coa_sale_id->id,
					'account_id'	=> $coa_sale_id->bp_journal ? $moi->account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $tax,
					'nominal_fc'	=> $tax,
					'note'			=> 'No Seri Pajak : '.$moi->tax_no,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			CustomHelper::addCountLimitCredit($moi->account_id,$balance); */

			if($moi->grandtotal == 0){
				$moi->update([
					'status'	=> '3'
				]);
			}

			$journal = Journal::find($query->id);
			$journal->note = $journal->note.' - '.implode(', ',$arrNote);
			$journal->save();

			$moi->marketingOrderDeliveryProcess->update([
				'status'	=> '3'
			]);

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
			// $coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$mom->company_id)->first();// tdk usa
			// $coahpp = Coa::where('code','500.01.01.01.01')->where('company_id',$mom->company_id)->first();// tdk usa

            $total = round($mom->total * $mom->currency_rate,2);
            $tax = round($mom->tax * $mom->currency_rate,2);

            if($total > 0){
                JournalDetail::create([
                    'journal_id'	=> $query->id,
                    'coa_id'		=> $coapotonganpenjualan->id,
                    'account_id'	=> $coapotonganpenjualan->bp_journal ? $mom->account_id : NULL,
                    'type'			=> '1',
                    'nominal'		=> $total,
                    'nominal_fc'    => $mom->total,
                ]);
            }

            if($tax > 0){
                JournalDetail::create([
                    'journal_id'	=> $query->id,
                    'coa_id'		=> $mom->taxMaster->coa_sale_id,
                    'account_id'	=> $mom->taxMaster->coaSale->bp_journal ? $mom->account_id : NULL,
                    'type'			=> '1',
                    'nominal'		=> $tax,
                    'note'			=> 'No Seri Pajak : '.$mom->tax_no,

                    'nominal_fc'    => $mom->tax,
                ]);
            }

            if($mom->grandtotal > 0){
                JournalDetail::create([
                    'journal_id'	=> $query->id,
                    'coa_id'		=> $coapiutang->id,
                    'account_id'	=> $coapiutang->bp_journal ? $mom->account_id : NULL,
                    'type'			=> '2',
                    'nominal'		=> $mom->grandtotal * $mom->currency_rate,
                    'nominal_fc'    => $mom->grandtotal,
                ]);

            }


/*
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

					// if($totalDebit !== $totalCredit){
					// 	$balance = $totalDebit - $totalCredit;

					// 	JournalDetail::create([
					// 		'journal_id'	=> $query->id,
					// 		'coa_id'		=> $coarounding->id,
					// 		'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
					// 		'type'			=> $balance > 0 ? '2' : '1',
					// 		'nominal'		=> -1 * $balance,
					// 	]);
					// }

					// if($mom->type == '2'){
					// 	$hpp = $row->lookable->lookable->getPriceHpp() * $row->qty;

					// 	JournalDetail::create([
					// 		'journal_id'	=> $query->id,
					// 		'account_id'	=> $mom->account_id,
					// 		'coa_id'		=> $row->lookable->lookable->itemStock->item->itemGroup->coa_id,
					// 		'place_id'		=> $row->lookable->lookable->place_id,
					// 		'item_id'		=> $row->lookable->lookable->item_id,
					// 		'warehouse_id'	=> $row->lookable->lookable->warehouse_id,
					// 		'type'			=> '1',
					// 		'nominal'		=> $hpp,
					// 	]);

					// 	JournalDetail::create([
					// 		'journal_id'	=> $query->id,
					// 		'account_id'	=> $coahpp->bp_journal ? $mom->account_id : NULL,
					// 		'coa_id'		=> $coahpp->id,
					// 		'place_id'		=> $row->lookable->lookable->place_id,
					// 		'item_id'		=> $row->lookable->lookable->item_id,
					// 		'warehouse_id'	=> $row->lookable->lookable->warehouse_id,
					// 		'type'			=> '2',
					// 		'nominal'		=> $hpp,
					// 	]);

					// 	self::sendCogs($table_name,
					// 		$mom->id,
					// 		$row->lookable->lookable->place->company_id,
					// 		$row->lookable->lookable->place_id,
					// 		$row->lookable->lookable->warehouse_id,
					// 		$row->lookable->lookable->item_id,
					// 		$row->qty * $row->lookable->lookable->item->sell_convert,
					// 		$hpp,
					// 		'IN',
					// 		$mom->post_date,
					// 		$row->lookable->lookable->area_id,
					// 		NULL,
					// 		NULL,
					// 	);

					// 	self::sendStock(
					// 		$row->lookable->lookable->place_id,
					// 		$row->lookable->lookable->warehouse_id,
					// 		$row->lookable->lookable->item_id,
					// 		$row->qty * $row->lookable->lookable->item->sell_convert,
					// 		'IN',
					// 		$row->lookable->lookable->area_id,
					// 		NULL,
					// 		NULL,
					// 	);
					// }
				}

				CustomHelper::removeCountLimitCredit($mom->account_id,$row->grandtotal);
			} */
/*
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
			} */

		}elseif($table_name == 'purchase_invoices'){
			#self::removeJournal($table_name,$table_id);
			#start untuk po tipe biaya / jasa
			$totalOutSide = 0;

			$pi = PurchaseInvoice::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pi->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> $pi->currency_id,
				'currency_rate'	=> $pi->currency_rate,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note,
				'status'		=> '3'
			]);

			$coauangmukapembelian = Coa::where('code','100.01.07.01.01')->where('company_id',$pi->company_id)->first();
			$coahutangbelumditagih = Coa::where('code','200.01.03.01.02')->where('company_id',$pi->company_id)->first();
			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pi->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$pi->company_id)->first();
			$coaselisihkurs = Coa::where('code','700.01.01.01.02')->where('company_id',$pi->company_id)->first();

			$grandtotal = 0;
			$tax = 0;
			$wtax = 0;
			$currency_rate = 1;
			$realInvoice = 0;
			$realDownPayment = 0;
			$adjustLandedCost = 0;
			$adjustGrpo = 0;

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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

				}elseif($row->lookable_type == 'landed_cost_fee_details'){
					$type = $pi->currency->type;
					$currency_rate = $row->lookable->landedCost->currency_rate;
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->lookable->landedCostFee->coa_id,
						'account_id'	=> $row->lookable->landedCostFee->coa->bp_journal ? $row->lookable->landedCost->account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->lookable->total * $currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->lookable->total * $currency_rate : $row->lookable->total,
						'note'			=> $row->lookable->landedCostFee->name,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

					$grandtotal += $row->grandtotal * $currency_rate;
					$tax += $row->tax * $currency_rate;
					$wtax += $row->wtax * $currency_rate;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->tax * $currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $currency_rate : $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
					}

					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->wtax * $currency_rate,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->wtax * $currency_rate : $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
					}

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->grandtotal * $currency_rate,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $currency_rate : $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

					$adjustLandedCost += (($row->grandtotal * $currency_rate) - ($row->grandtotal * $pi->currency_rate));
				}else{
					$type = $pi->currency->type;

					$currency_rate = $row->lookable->goodReceipt->latestCurrencyRateByDate($pi->post_date);

					$totalgrpo = round($row->total * $currency_rate,2);
					$totalinvoice = round($row->total * $pi->currency_rate,2);
					$balancegrpo = $totalgrpo - $totalinvoice;

					if($balancegrpo > 0 || $balancegrpo < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaselisihkurs->id,
							'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
							'type'			=> $balancegrpo > 0  ? '2' : '1',
							'nominal'		=> floatval(abs($balancegrpo)),
							'nominal_fc'	=> 0,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
					}

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
						'nominal'		=> $totalgrpo,
						'nominal_fc'	=> $type == '1' || $type == '' ? $totalgrpo : $row->total,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
							'nominal'		=> round($row->tax * $pi->currency_rate,2),
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $pi->currency_rate : $row->tax,
							'note'			=> $row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
							'nominal'		=> round($row->wtax * $pi->currency_rate,2),
							'nominal_fc'	=> $type == '1' || $type == '2' ? $row->wtax * $pi->currency_rate : $row->wtax,
							'note'			=> $row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> $row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
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
						'nominal'		=> round($row->grandtotal * $pi->currency_rate,2),
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $pi->currency_rate : $row->grandtotal,
						'note'			=> $row->note,
						'note2'			=> $row->note2,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

					$adjustGrpo += round($row->grandtotal * $pi->currency_rate,2);
				}
			}

			if($adjustGrpo > 0 && $pi->currency->type == '2'){
				$balanceselisih = $adjustGrpo - round($pi->grandtotal * $pi->currency_rate,2);
				if($balanceselisih < 0 || $balanceselisih > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coarounding->id,
						'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
						'type'			=> $balanceselisih > 0 ? '1' : '2',
						'nominal'		=> abs($balanceselisih),
						'nominal_fc'	=> 0,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'note'			=> 'AUTO ADJUST SELISIH RUPIAH GRPO & APIN KURS ASING'
					]);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> $balanceselisih > 0 ? '2' : '1',
						'nominal'		=> abs($balanceselisih),
						'nominal_fc'	=> 0,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'note'			=> 'AUTO ADJUST SELISIH RUPIAH GRPO & APIN KURS ASING'
					]);
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
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coahutangusaha->id,
					'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
					'type'			=> $pi->rounding > 0 ? '2' : '1',
					'nominal'		=> abs($pi->rounding * $currency_rate),
					'nominal_fc'	=> $type == '1' || $type == '' ? abs($pi->rounding * $currency_rate) : abs($pi->rounding),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			#start journal down payment

			if($pi->downpayment > 0){
				foreach($pi->purchaseInvoiceDp as $row){
					/* $downpayment += $row->nominal * $row->purchaseDownPayment->currency_rate; */
					$currencydp = $row->purchaseDownPayment->latestCurrencyRate();

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->nominal * $currencydp,
						'nominal_fc'	=> $row->purchaseDownPayment->currency->type == '1' || $row->purchaseDownPayment->currency->type == '' ? $row->nominal * $currencydp : $row->nominal,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coauangmukapembelian->id,
						'account_id'	=> $coauangmukapembelian->bp_journal ? $account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->nominal * $currencydp,
						'nominal_fc'	=> $row->purchaseDownPayment->currency->type == '1' || $row->purchaseDownPayment->currency->type == '' ? $row->nominal * $currencydp : $row->nominal,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);
					$realDownPayment += $row->nominal * $currencydp;
					$realInvoice += $row->nominal * $pi->currency_rate;

					if($row->purchaseDownPayment->balanceInvoice() <= 0){
						foreach($row->purchaseDownPayment->purchaseDownPaymentDetail as $rowdpdetail){
							if($rowdpdetail->fundRequestDetail()->exists()){
								$rowdpdetail->fundRequestDetail->fundRequest->update([
									'balance_status'	=> '1'
								]);
							}
							$row->purchaseDownPayment->update([
								'balance_status'	=> '1',
							]);
						}
					}
				}

				$balanceKurs = $realDownPayment - $realInvoice - $adjustLandedCost;

				if($balanceKurs > 0 || $balanceKurs < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coaselisihkurs->id,
						'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
						'type'			=> $balanceKurs > 0  ? '1' : '2',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
					]);
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
						'type'			=> $balanceKurs > 0  ? '2' : '1',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
					]);
				}
			}

			$pi->updateRootDocumentStatusDone();

			if($pi->balance == 0){
				$pi->update([
					'status'	=> '3',
					'done_date'	=> date('Y-m-d H:i:s'),
					'done_note'	=> 'DITUTUP OLEH SISTEM'
				]);
			}

		}elseif($table_name == 'marketing_order_down_payments'){

			$modp = MarketingOrderDownPayment::find($table_id);

			/* $coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$modp->company_id)->first();
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
				'nominal_fc'	=> $modp->grandtotal,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coauangmuka->id,
				'account_id'	=> $coauangmuka->bp_journal ? $account_id : NULL,
				'type'			=> '2',
				'nominal'		=> $modp->total * $data->currency_rate,
				'nominal_fc'	=> $modp->total,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			if($modp->tax > 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $modp->taxId->coa_sale_id,
					'account_id'	=> $modp->taxId->coaSale->bp_journal ? $account_id : NULL,
					'type'			=> '2',
					'nominal'		=> $modp->tax * $data->currency_rate,
					'nominal_fc'	=> $modp->tax,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			} */

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
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coahutangusaha->id,
				'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
				'type'			=> '2',
				'nominal'		=> $pdp->grandtotal * $pdp->currency_rate,
				'nominal_fc'	=> $pdp->currency->type == '1' ? $pdp->grandtotal * $pdp->currency_rate : $pdp->grandtotal,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			CustomHelper::addDeposit($account_id,$pdp->grandtotal * $pdp->currency_rate);

		}elseif($table_name == 'employee_transfers'){
			$transfer = EmployeeTransfer::find($table_id);

			self::updateEmployeeTransfer($transfer);

		}elseif($table_name == 'production_issues'){
			$pir = ProductionIssue::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pir->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $pir->note ?? '',
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			$total = 0;
			$parentFg = false;
			$coawip = Coa::where('code','100.01.04.03.01')->where('company_id',$pir->company_id)->first();
			$arrBom = [];

			foreach($pir->productionIssueDetail as $row){
				if($row->lookable_type == 'items' && $row->is_wip){
					$parentFg = true;
				}
				if($row->bom()->exists()){
					if(!in_array($row->bom_id,$arrBom)){
						$arrBom[] = $row->bom_id;
					}
				}
				if(!$row->is_wip){
					$total += $row->total;
				}
			}

			if(!$parentFg){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coawip->id,
					'line_id'		=> $pir->line_id,
					'place_id'		=> $pir->place_id,
					'machine_id'	=> $pir->machine_id,
					'type'			=> '1',
					'nominal'		=> $total,
					'nominal_fc'	=> $total,
					'note'			=> $pir->productionOrderDetail->productionOrder->code,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
				]);
			}

			#lek misal item receive fg kelompokkan dri child
			if($pir->productionFgReceive()->exists() && count($arrBom) > 0){
				/* JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coawip->id,
					'line_id'		=> $pir->line_id,
					'place_id'		=> $pir->place_id,
					'machine_id'	=> $pir->machine_id,
					'type'			=> '1',
					'nominal'		=> $total,
					'nominal_fc'	=> $total,
					'note'			=> $pir->productionOrderDetail->productionOrder->code,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]); */
				foreach($arrBom as $rowbom){
					foreach($pir->productionIssueDetail()->whereNull('is_wip')->where('bom_id',$rowbom)->orderBy('id')->get() as $row){
						if($row->lookable_type == 'items'){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $row->lookable->itemGroup->coa_id,
								'place_id'		=> $row->itemStock->place_id,
								'line_id'		=> $row->productionIssue->line_id,
								'item_id'		=> $row->itemStock->item_id,
								'warehouse_id'	=> $row->itemStock->warehouse_id,
								'type'			=> '2',
								'nominal'		=> $row->total,
								'nominal_fc'	=> $row->total,
								'note'			=> $pir->productionOrderDetail->productionOrder->code,
								'lookable_type'	=> $table_name,
								'lookable_id'	=> $table_id,
								'detailable_type'=> $row->getTable(),
								'detailable_id'	=> $row->id,
							]);

							self::sendCogs($table_name,
								$pir->id,
								$pir->company_id,
								$row->itemStock->place_id,
								$row->itemStock->warehouse_id,
								$row->itemStock->item_id,
								$row->qty,
								$row->total,
								'OUT',
								$pir->post_date,
								$row->itemStock->area_id,
								$row->itemStock->item_shading_id,
								$row->itemStock->production_batch_id,
								$row->getTable(),
								$row->id,
							);

							self::sendStock(
								$row->itemStock->place_id,
								$row->itemStock->warehouse_id,
								$row->itemStock->item_id,
								$row->qty,
								'OUT',
								$row->itemStock->area_id,
								$row->itemStock->item_shading_id,
								$row->itemStock->production_batch_id,
							);
						}elseif($row->lookable_type == 'resources'){
							if($row->bomDetail()->exists()){
								if($row->bomDetail->cost_distribution_id){
									$lastIndex = count($row->bomDetail->costDistribution->costDistributionDetail) - 1;
									$accumulation = 0;
									foreach($row->bomDetail->costDistribution->costDistributionDetail as $key => $rowcost){
										if($key == $lastIndex){
											$nominal = $row->total - $accumulation;
										}else{
											$nominal = round(($rowcost->percentage / 100) * $row->total);
											$accumulation += $nominal;
										}
										JournalDetail::create([
											'journal_id'                    => $query->id,
											'cost_distribution_detail_id'   => $rowcost->id,
											'coa_id'						=> $row->lookable->coa_id,
											'place_id'                      => $rowcost->place_id ?? ($pir->place_id ?? NULL),
											'line_id'                       => $rowcost->line_id ?? ($pir->line_id ?? NULL),
											'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
											'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
											'type'                          => '2',
											'nominal'						=> $nominal,
											'nominal_fc'					=> $nominal,
											'note'							=> $pir->productionOrderDetail->productionOrder->code,
											'lookable_type'					=> $table_name,
											'lookable_id'					=> $table_id,
											'detailable_type'				=> $row->getTable(),
											'detailable_id'					=> $row->id,
										]);
									}
								}else{
									JournalDetail::create([
										'journal_id'	=> $query->id,
										'coa_id'		=> $row->lookable->coa_id,
										'line_id'		=> $pir->line_id,
										'place_id'		=> $pir->place_id,
										'type'			=> '2',
										'nominal'		=> $row->total,
										'nominal_fc'	=> $row->total,
										'note'			=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'	=> $table_name,
										'lookable_id'	=> $table_id,
										'detailable_type'=> $row->getTable(),
										'detailable_id'	=> $row->id,
									]);
								}
							}else{
								if($row->cost_distribution_id){
									$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
									$accumulation = 0;
									foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
										if($key == $lastIndex){
											$nominal = $row->total - $accumulation;
										}else{
											$nominal = round(($rowcost->percentage / 100) * $row->total);
											$accumulation += $nominal;
										}
										JournalDetail::create([
											'journal_id'                    => $query->id,
											'cost_distribution_detail_id'   => $rowcost->id,
											'coa_id'						=> $row->lookable->coa_id,
											'place_id'                      => $rowcost->place_id ?? ($pir->place_id ?? NULL),
											'line_id'                       => $rowcost->line_id ?? ($pir->line_id ?? NULL),
											'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
											'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
											'type'                          => '2',
											'nominal'						=> $nominal,
											'nominal_fc'					=> $nominal,
											'note'							=> $pir->productionOrderDetail->productionOrder->code,
											'lookable_type'					=> $table_name,
											'lookable_id'					=> $table_id,
											'detailable_type'				=> $row->getTable(),
											'detailable_id'					=> $row->id,
										]);
									}
								}else{
									JournalDetail::create([
										'journal_id'	=> $query->id,
										'coa_id'		=> $row->lookable->coa_id,
										'line_id'		=> $pir->line_id,
										'place_id'		=> $pir->place_id,
										'type'			=> '2',
										'nominal'		=> $row->total,
										'nominal_fc'	=> $row->total,
										'note'			=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'	=> $table_name,
										'lookable_id'	=> $table_id,
										'detailable_type'=> $row->getTable(),
										'detailable_id'	=> $row->id,
									]);
								}
							}
						}
					}
				}
			}else{

				foreach($pir->productionIssueDetail()->orderBy('id')->get() as $row){
					if($row->lookable_type == 'items'){
						if($row->is_wip){
							//do nothing
						}else{
							if($row->productionBatchUsage()->exists()){
								foreach($row->productionBatchUsage as $rowbatchusage){
									if($row->bom->group == '1'){
										$price = $row->lookable->priceNowProduction($row->place_id,$pir->post_date);
										$rowtotal = round($price * $rowbatchusage->qty,2);
									}else{
										$rowtotal = $rowbatchusage->productionBatch->totalById($rowbatchusage->id);
									}
									JournalDetail::create([
										'journal_id'	=> $query->id,
										'coa_id'		=> $rowbatchusage->productionBatch->item->itemGroup->coa_id,
										'place_id'		=> $rowbatchusage->productionBatch->place_id,
										'line_id'		=> $row->productionIssue->line_id,
										'item_id'		=> $rowbatchusage->productionBatch->item_id,
										'warehouse_id'	=> $rowbatchusage->productionBatch->warehouse_id,
										'type'			=> '2',
										'nominal'		=> $rowtotal,
										'nominal_fc'	=> $rowtotal,
										'note'			=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'	=> $table_name,
										'lookable_id'	=> $table_id,
										'detailable_type'=> $rowbatchusage->getTable(),
										'detailable_id'	=> $rowbatchusage->id,
									]);

									self::sendCogs($table_name,
										$pir->id,
										$pir->company_id,
										$rowbatchusage->productionBatch->place_id,
										$rowbatchusage->productionBatch->warehouse_id,
										$rowbatchusage->productionBatch->item_id, #sampek sini
										$rowbatchusage->qty,
										$rowtotal,
										'OUT',
										$pir->post_date,
										NULL,
										NULL,
										$rowbatchusage->production_batch_id,
										$rowbatchusage->getTable(),
										$rowbatchusage->id,
									);

									self::sendStock(
										$rowbatchusage->productionBatch->place_id,
										$rowbatchusage->productionBatch->warehouse_id,
										$rowbatchusage->productionBatch->item_id,
										$rowbatchusage->qty,
										'OUT',
										NULL,
										NULL,
										$rowbatchusage->production_batch_id,
									);
								}
							}else{
								#jika wip sebelum final ke wip final jurnal batchnya disini
								if($pir->productionFgReceive()->exists()){
									foreach($pir->productionFgReceive->productionBatchUsage()->whereHas('productionBatch',function($querykuy)use($row){
										$querykuy->where('item_id',$row->lookable_id);
									})->get() as $rowbatchusage){
										$totalCost = round(($rowbatchusage->productionBatch->total / $rowbatchusage->productionBatch->qty_real) * $rowbatchusage->qty,2);

										JournalDetail::create([
											'journal_id'	=> $query->id,
											'coa_id'		=> $rowbatchusage->productionBatch->item->itemGroup->coa_id,
											'place_id'		=> $rowbatchusage->productionBatch->place_id,
											'line_id'		=> $pir->productionFgReceive->line_id,
											'item_id'		=> $rowbatchusage->productionBatch->item_id,
											'warehouse_id'	=> $rowbatchusage->productionBatch->warehouse_id,
											'type'			=> '2',
											'nominal'		=> $totalCost,
											'nominal_fc'	=> $totalCost,
											'note'			=> $pir->productionOrderDetail->productionOrder->code,
											'lookable_type'	=> $table_name,
											'lookable_id'	=> $table_id,
											'detailable_type'=> $rowbatchusage->getTable(),
											'detailable_id'	=> $rowbatchusage->id,
										]);

										self::sendCogs($table_name,
											$pir->id,
											$pir->company_id,
											$rowbatchusage->productionBatch->place_id,
											$rowbatchusage->productionBatch->warehouse_id,
											$rowbatchusage->productionBatch->item_id,
											$rowbatchusage->qty,
											$totalCost,
											'OUT',
											$pir->post_date,
											NULL,
											NULL,
											$rowbatchusage->productionBatch->id,
											$rowbatchusage->getTable(),
											$rowbatchusage->id,
										);

										self::sendStock(
											$rowbatchusage->productionBatch->place_id,
											$rowbatchusage->productionBatch->warehouse_id,
											$rowbatchusage->productionBatch->item_id,
											$rowbatchusage->qty,
											'OUT',
											NULL,
											NULL,
											$rowbatchusage->productionBatch->id,
										);
									}
								}else{
									#jika production issue biasa
									JournalDetail::create([
										'journal_id'	=> $query->id,
										'coa_id'		=> $row->lookable->itemGroup->coa_id,
										'place_id'		=> $row->itemStock->place_id,
										'line_id'		=> $row->productionIssue->line_id,
										'item_id'		=> $row->itemStock->item_id,
										'warehouse_id'	=> $row->itemStock->warehouse_id,
										'type'			=> '2',
										'nominal'		=> $row->total,
										'nominal_fc'	=> $row->total,
										'note'			=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'	=> $table_name,
										'lookable_id'	=> $table_id,
										'detailable_type'=> $row->getTable(),
										'detailable_id'	=> $row->id,
									]);

									self::sendCogs($table_name,
										$pir->id,
										$pir->company_id,
										$row->itemStock->place_id,
										$row->itemStock->warehouse_id,
										$row->itemStock->item_id,
										$row->qty,
										$row->total,
										'OUT',
										$pir->post_date,
										NULL,
										NULL,
										NULL,
										$row->getTable(),
										$row->id,
									);

									self::sendStock(
										$row->itemStock->place_id,
										$row->itemStock->warehouse_id,
										$row->itemStock->item_id,
										$row->qty,
										'OUT',
										NULL,
										NULL,
										NULL,
									);
								}
							}
						}
					}elseif($row->lookable_type == 'resources'){
						if($row->bomDetail()->exists()){
							if($row->bomDetail->cost_distribution_id){
								$lastIndex = count($row->bomDetail->costDistribution->costDistributionDetail) - 1;
								$accumulation = 0;
								foreach($row->bomDetail->costDistribution->costDistributionDetail as $key => $rowcost){
									if($key == $lastIndex){
										$nominal = $row->total - $accumulation;
									}else{
										$nominal = round(($rowcost->percentage / 100) * $row->total);
										$accumulation += $nominal;
									}
									JournalDetail::create([
										'journal_id'                    => $query->id,
										'cost_distribution_detail_id'   => $rowcost->id,
										'coa_id'						=> $row->lookable->coa_id,
										'place_id'                      => $rowcost->place_id ?? ($pir->place_id ?? NULL),
										'line_id'                       => $rowcost->line_id ?? ($pir->line_id ?? NULL),
										'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
										'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
										'type'                          => '2',
										'nominal'						=> $nominal,
										'nominal_fc'					=> $nominal,
										'note'							=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'					=> $table_name,
										'lookable_id'					=> $table_id,
										'detailable_type'				=> $row->getTable(),
										'detailable_id'					=> $row->id,
									]);
								}
							}else{
								JournalDetail::create([
									'journal_id'	=> $query->id,
									'coa_id'		=> $row->lookable->coa_id,
									'line_id'		=> $pir->line_id,
									'place_id'		=> $pir->place_id,
									'type'			=> '2',
									'nominal'		=> $row->total,
									'nominal_fc'	=> $row->total,
									'note'			=> $pir->productionOrderDetail->productionOrder->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
							}
						}else{
							if($row->cost_distribution_id){
								$lastIndex = count($row->costDistribution->costDistributionDetail) - 1;
								$accumulation = 0;
								foreach($row->costDistribution->costDistributionDetail as $key => $rowcost){
									if($key == $lastIndex){
										$nominal = $row->total - $accumulation;
									}else{
										$nominal = round(($rowcost->percentage / 100) * $row->total);
										$accumulation += $nominal;
									}
									JournalDetail::create([
										'journal_id'                    => $query->id,
										'cost_distribution_detail_id'   => $rowcost->id,
										'coa_id'						=> $row->lookable->coa_id,
										'place_id'                      => $rowcost->place_id ?? ($pir->place_id ?? NULL),
										'line_id'                       => $rowcost->line_id ?? ($pir->line_id ?? NULL),
										'machine_id'                    => $rowcost->machine_id ? $rowcost->machine_id : NULL,
										'department_id'                 => $rowcost->department_id ? $rowcost->department_id : NULL,
										'type'                          => '2',
										'nominal'						=> $nominal,
										'nominal_fc'					=> $nominal,
										'note'							=> $pir->productionOrderDetail->productionOrder->code,
										'lookable_type'					=> $table_name,
										'lookable_id'					=> $table_id,
										'detailable_type'				=> $row->getTable(),
										'detailable_id'					=> $row->id,
									]);
								}
							}else{
								JournalDetail::create([
									'journal_id'	=> $query->id,
									'coa_id'		=> $row->lookable->coa_id,
									'line_id'		=> $pir->line_id,
									'place_id'		=> $pir->place_id,
									'type'			=> '2',
									'nominal'		=> $row->total,
									'nominal_fc'	=> $row->total,
									'note'			=> $pir->productionOrderDetail->productionOrder->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
							}
						}
					}
				}
			}

			if($parentFg){
				$pir->update([
					'status'	=> '3'
				]);
			}else{
				if($pir->productionFgReceive()->exists()){
					$pir->update([
						'status'	=> '3'
					]);
				}
			}
		}elseif($table_name == 'production_receives'){

			$pir = ProductionReceive::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pir->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note ?? '',
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			$total = 0;

			$coawip = Coa::where('code','100.01.04.03.01')->where('company_id',$pir->company_id)->first();

			foreach($pir->productionReceiveDetail as $row){
				if($row->productionBatch()->exists()){
					foreach($row->productionBatch as $rowbatch){
						$total += $rowbatch->total;
					}
				}else{
					$total += $row->total;
				}
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coawip->id,
				'line_id'		=> $pir->line_id,
				'place_id'		=> $pir->place_id,
				'type'			=> '2',
				'nominal'		=> $total,
				'nominal_fc'	=> $total,
				'note'			=> $pir->code,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			foreach($pir->productionReceiveDetail as $row){
				if($row->productionBatch()->exists()){
					foreach($row->productionBatch as $rowbatch){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->item->itemGroup->coa_id,
							'place_id'		=> $row->place_id,
							'line_id'		=> $row->productionReceive->line_id,
							'item_id'		=> $row->item_id,
							'warehouse_id'	=> $row->warehouse_id,
							'type'			=> '1',
							'nominal'		=> $rowbatch->total,
							'nominal_fc'	=> $rowbatch->total,
							'note'			=> $pir->code,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $rowbatch->getTable(),
							'detailable_id'	=> $rowbatch->id,
						]);

						self::sendCogs($table_name,
							$pir->id,
							$pir->company_id,
							$row->place_id,
							$row->warehouse_id,
							$row->item_id,
							$rowbatch->qty_real,
							$rowbatch->total,
							'IN',
							$pir->post_date,
							NULL,
							NULL,
							$rowbatch->id,
							$rowbatch->getTable(),
							$rowbatch->id,
						);

						self::sendStock(
							$row->place_id,
							$row->warehouse_id,
							$row->item_id,
							$rowbatch->qty_real,
							'IN',
							NULL,
							NULL,
							$rowbatch->id,
						);
					}
				}else{
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->item->itemGroup->coa_id,
						'place_id'		=> $row->place_id,
						'line_id'		=> $row->productionReceive->line_id,
						'item_id'		=> $row->item_id,
						'warehouse_id'	=> $row->warehouse_id,
						'type'			=> '1',
						'nominal'		=> $row->total,
						'nominal_fc'	=> $row->total,
						'note'			=> $pir->code,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
					]);

					self::sendCogs($table_name,
						$pir->id,
						$pir->company_id,
						$row->place_id,
						$row->warehouse_id,
						$row->item_id,
						$row->qty,
						$row->total,
						'IN',
						$pir->post_date,
						NULL,
						NULL,
						NULL,
						$row->getTable(),
						$row->id,
					);

					self::sendStock(
						$row->place_id,
						$row->warehouse_id,
						$row->item_id,
						$row->qty,
						'IN',
						NULL,
						NULL,
						NULL,
					);
				}

				if($row->qty_reject > 0){
					if($pir->productionOrderDetail->productionScheduleDetail->bom->itemReject()->exists()){
						self::sendCogs($table_name,
							$pir->id,
							$pir->company_id,
							$pir->place_id,
							$pir->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
							$pir->productionOrderDetail->productionScheduleDetail->bom->item_reject_id,
							$row->qty_reject,
							0,
							'IN',
							$pir->post_date,
							NULL,
							NULL,
							NULL,
							$row->getTable(),
							$row->id,
						);

						self::sendStock(
							$pir->place_id,
							$pir->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
							$pir->productionOrderDetail->productionScheduleDetail->bom->item_reject_id,
							$row->qty_reject,
							'IN',
							NULL,
							NULL,
							NULL,
						);
					}
				}
			}

			$pir->update([
				'status'	=> '3'
			]);

			foreach($pir->productionReceiveIssue as $row){
				if($row->productionIssue->balanceQtyGr() <= 0){
					$row->productionIssue->update([
						'status'	=> '3'
					]);
				}
			}
		}elseif($table_name == 'production_barcodes'){



		}elseif($table_name == 'production_fg_receives'){

			$pir = ProductionFgReceive::find($table_id);

			$pir->createProductionIssue();
			$pir->recalculate();

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pir->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note ?? '',
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			$total = 0;

			$coawip = Coa::where('code','100.01.04.03.01')->where('company_id',$pir->company_id)->first();

			$qtyWip5 = 0;
			foreach($pir->productionFgReceiveDetail as $row){
				$qtyWip5 += $row->qty;
				$total += $row->total;
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $pir->productionOrderDetail->productionScheduleDetail->item->itemGroup->coa_id,
				'line_id'		=> $pir->line_id,
				'place_id'		=> $pir->place_id,
				'warehouse_id'	=> $pir->productionOrderDetail->productionScheduleDetail->item->warehouse(),
				'type'			=> '1',
				'nominal'		=> $total,
				'nominal_fc'	=> $total,
				'note'			=> $pir->code,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coawip->id,
				'line_id'		=> $pir->line_id,
				'place_id'		=> $pir->place_id,
				'machine_id'	=> $pir->machine_id,
				'type'			=> '2',
				'nominal'		=> $total,
				'nominal_fc'	=> $total,
				'note'			=> $pir->code,
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
			]);

			self::sendCogs($table_name,
				$pir->id,
				$pir->company_id,
				$pir->place_id,
				$pir->productionOrderDetail->productionScheduleDetail->item->warehouse(),
				$pir->productionOrderDetail->productionScheduleDetail->item_id,
				$qtyWip5,
				$total,
				'IN',
				$pir->post_date,
				NULL,
				NULL,
				NULL,
				$pir->getTable(),
				$pir->id
			);

			self::sendStock(
				$pir->place_id,
				$pir->productionOrderDetail->productionScheduleDetail->item->warehouse(),
				$pir->productionOrderDetail->productionScheduleDetail->item_id,
				$qtyWip5,
				'IN',
				NULL,
				NULL,
				NULL,
			);

			if($pir->qty_reject > 0){
				if($pir->productionOrderDetail->productionScheduleDetail->bom->itemReject()->exists()){
					self::sendCogs($table_name,
						$pir->id,
						$pir->company_id,
						$pir->place_id,
						$pir->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
						$pir->productionOrderDetail->productionScheduleDetail->bom->item_reject_id,
						$pir->qty_reject,
						0,
						'IN',
						$pir->post_date,
						NULL,
						NULL,
						NULL,
						$pir->getTable(),
						$pir->id
					);

					self::sendStock(
						$pir->place_id,
						$pir->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
						$pir->productionOrderDetail->productionScheduleDetail->bom->item_reject_id,
						$pir->qty_reject,
						'IN',
						NULL,
						NULL,
						NULL,
					);
				}
			}

			foreach($pir->productionFgReceiveDetail as $row){
				if($row->productionBarcodeDetail()->exists()){
					if($row->productionBarcodeDetail->productionBarcode->alreadyReceived()){
						$row->productionBarcodeDetail->productionBarcode->update([
							'status'	=> '3'
						]);
					}
				}
			}

		}elseif($table_name == 'production_handovers'){

			$pir = ProductionHandover::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pir->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note ?? '',
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			$total = 0;

			foreach($pir->productionHandoverDetail as $row){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->item->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'line_id'		=> $pir->productionFgReceive->line_id,
					'item_id'		=> $row->item_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $row->total,
					'nominal_fc'	=> $row->total,
					'note'			=> $pir->code,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);

				self::sendCogs($table_name,
					$pir->id,
					$pir->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					round($row->qty * $row->productionFgReceiveDetail->conversion,3),
					$row->total,
					'IN',
					$pir->post_date,
					$row->area_id,
					$row->item_shading_id,
					$row->productionBatch->id,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					round($row->qty * $row->productionFgReceiveDetail->conversion,3),
					'IN',
					$row->area_id,
					$row->item_shading_id,
					$row->productionBatch->id,
				);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->productionFgReceiveDetail->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'line_id'		=> $pir->productionFgReceive->line_id,
					'item_id'		=> $row->item_id,
					'warehouse_id'	=> $row->productionFgReceiveDetail->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->warehouse(),
					'type'			=> '2',
					'nominal'		=> $row->total,
					'nominal_fc'	=> $row->total,
					'note'			=> $pir->code,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);


				self::sendCogs($table_name,
					$pir->id,
					$pir->company_id,
					$pir->productionFgReceive->place_id,
					$pir->productionFgReceive->item->warehouse(),
					$pir->productionFgReceive->item_id,
					round($row->qty * $row->productionFgReceiveDetail->conversion,3),
					$row->total,
					'OUT',
					$pir->post_date,
					NULL,
					NULL,
					NULL,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$pir->productionFgReceive->place_id,
					$pir->productionFgReceive->item->warehouse(),
					$pir->productionFgReceive->item_id,
					round($row->qty * $row->productionFgReceiveDetail->conversion,3),
					'OUT',
					NULL,
					NULL,
					NULL,
				);
			}

			$pir->update([
				'status'	=> '3'
			]);

			if(!$pir->hasBalanceReceiveFg()){
				$pir->productionFgReceive->update([
					'status'	=> '3'
				]);
			}

		}elseif($table_name == 'production_repacks'){

			$pr = ProductionRepack::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pr->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->note ?? '',
				'status'		=> '3',
				'currency_rate'	=> 1,
				'currency_id'	=> 1,
			]);

			foreach($pr->productionRepackDetail as $row){
				#jurnal barang keluar
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->itemStock->item->itemGroup->coa_id,
					'place_id'		=> $row->itemStock->place_id,
					'item_id'		=> $row->item_source_id,
					'warehouse_id'	=> $row->itemStock->warehouse_id,
					'type'			=> '2',
					'nominal'		=> $row->total,
					'nominal_fc'	=> 0,
					'note'			=> $pr->code,
					'note2'			=> $row->itemStock->item->code.' - '.$row->itemStock->item->name,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);

				self::sendCogs($table_name,
					$pr->id,
					$pr->company_id,
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->item_source_id,
					$row->qty,
					$row->total,
					'OUT',
					$pr->post_date,
					$row->itemStock->area_id,
					$row->itemStock->item_shading_id,
					$row->itemStock->production_batch_id,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->itemStock->place_id,
					$row->itemStock->warehouse_id,
					$row->itemStock->item_id,
					$row->qty,
					'OUT',
					$row->itemStock->area_id,
					$row->itemStock->item_shading_id,
					$row->itemStock->production_batch_id,
				);

				#jurnal barang masuk
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->itemTarget->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'item_id'		=> $row->item_target_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $row->total,
					'nominal_fc'	=> 0,
					'note'			=> $pr->code,
					'note2'			=> $row->itemTarget->code.' - '.$row->itemTarget->name,
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'detailable_type'=> $row->getTable(),
					'detailable_id'	=> $row->id,
				]);

				self::sendCogs($table_name,
					$pr->id,
					$pr->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_target_id,
					$row->qty,
					$row->total,
					'IN',
					$pr->post_date,
					$row->area_id,
					$row->item_shading_id,
					$row->production_batch_id,
					$row->getTable(),
					$row->id,
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_target_id,
					$row->qty,
					'IN',
					$row->area_id,
					$row->item_shading_id,
					$row->production_batch_id,
				);
			}

			$pr->update([
				'status'	=> '3'
			]);

		}elseif($table_name == 'adjust_rates'){
			$ar = AdjustRate::find($table_id);

			if($ar){
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'company_id'	=> $ar->company_id,
					'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'post_date'		=> $data->post_date,
					'note'			=> $ar->note,
					'currency_id'	=> $ar->currency_id,
					'currency_rate'	=> $ar->currency_rate,
					'status'		=> '3'
				]);

				$coaselisihkurs = Coa::where('code','700.01.01.01.03')->where('company_id',$ar->company_id)->first();

				foreach($ar->adjustRateDetail as $row){
					$nominal = abs($row->nominal);
					if($row->type == '1'){
						$totalBefore = round($row->nominal_fc * $row->nominal_rate,2);
						$totalNew = round($row->nominal_fc * $ar->currency_rate,2);
						$balance = $totalNew - $totalBefore;
						$nominal = abs($balance);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'type'			=> $row->nominal > 0 ? '1' : '2',
							'account_id'	=> $row->coa->bp_journal ? ($row->lookable->account_id ?? NULL) : NULL,
							'nominal'		=> $nominal,
							'nominal_fc'	=> 0,
							'note'			=> $row->lookable->code,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaselisihkurs->id,
							'type'			=> $row->nominal > 0 ? '2' : '1',
							'nominal'		=> $nominal,
							'nominal_fc'	=> 0,
							'note'			=> $row->lookable->code,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
						if($row->lookable_type == 'purchase_down_payments'){
							if($row->lookable->balanceInvoice() <= 0){
								$queryreverse = Journal::create([
									'user_id'		=> session('bo_id'),
									'company_id'	=> $ar->company_id,
									'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($ar->reverse_date)).'00'),
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'post_date'		=> $ar->reverse_date,
									'note'			=> $ar->code.' - '.$row->lookable->code,
									'currency_id'	=> 1,
									'currency_rate'	=> 1,
									'status'		=> '3'
								]);

								JournalDetail::create([
									'journal_id'	=> $queryreverse->id,
									'coa_id'		=> $row->coa_id,
									'type'			=> $row->nominal > 0 ? '2' : '1',
									'account_id'	=> $row->coa->bp_journal ? ($row->lookable->account_id ?? NULL) : NULL,
									'nominal'		=> $nominal,
									'nominal_fc'	=> 0,
									'note'			=> 'REVERSE*'.$row->lookable->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
								JournalDetail::create([
									'journal_id'	=> $queryreverse->id,
									'coa_id'		=> $coaselisihkurs->id,
									'type'			=> $row->nominal > 0 ? '1' : '2',
									'nominal'		=> $nominal,
									'nominal_fc'	=> 0,
									'note'			=> $row->lookable->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
							}
						}
					}
					if($row->type == '2'){
						$totalBefore = round($row->nominal_fc * $row->nominal_rate,2);
						$totalNew = round($row->nominal_fc * $ar->currency_rate,2);
						$balance = $totalNew - $totalBefore;
						$nominal = abs($balance);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'type'			=> $row->nominal > 0 ? '2' : '1',
							'account_id'	=> $row->coa->bp_journal ? ($row->lookable->account_id ?? NULL) : NULL,
							'nominal'		=> $nominal,
							'nominal_fc'	=> 0,
							'note'			=> $row->lookable->code,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaselisihkurs->id,
							'type'			=> $row->nominal > 0 ? '1' : '2',
							'nominal'		=> $nominal,
							'nominal_fc'	=> 0,
							'note'			=> $row->lookable->code,
							'lookable_type'	=> $table_name,
							'lookable_id'	=> $table_id,
							'detailable_type'=> $row->getTable(),
							'detailable_id'	=> $row->id,
						]);
						if($row->lookable_type == 'purchase_down_payments'){
							if($row->lookable->balancePayment() <= 0){
								$queryreverse = Journal::create([
									'user_id'		=> session('bo_id'),
									'company_id'	=> $ar->company_id,
									'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($ar->reverse_date)).'00'),
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'post_date'		=> $ar->reverse_date,
									'note'			=> $ar->code.' - '.$row->lookable->code,
									'currency_id'	=> 1,
									'currency_rate'	=> 1,
									'status'		=> '3'
								]);

								JournalDetail::create([
									'journal_id'	=> $queryreverse->id,
									'coa_id'		=> $row->coa_id,
									'type'			=> $row->nominal > 0 ? '1' : '2',
									'account_id'	=> $row->coa->bp_journal ? ($row->lookable->account_id ?? NULL) : NULL,
									'nominal'		=> $nominal,
									'nominal_fc'	=> 0,
									'note'			=> 'REVERSE*'.$row->lookable->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
								JournalDetail::create([
									'journal_id'	=> $queryreverse->id,
									'coa_id'		=> $coaselisihkurs->id,
									'type'			=> $row->nominal > 0 ? '2' : '1',
									'nominal'		=> $nominal,
									'nominal_fc'	=> 0,
									'note'			=> $row->lookable->code,
									'lookable_type'	=> $table_name,
									'lookable_id'	=> $table_id,
									'detailable_type'=> $row->getTable(),
									'detailable_id'	=> $row->id,
								]);
							}
						}
					}
					$row->update([
						'nominal'	=> $row->nominal < 0 ? -1 * $nominal : $nominal,
					]);
				}
			}

			$ar->update([
				'status'	=> '3'
			]);
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
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
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
				// if($po->account->email){
				// 	$data = [
				// 		'title'     => 'Print Purchase Order',
				// 		'data'      => $po
				// 	];
				// 	$opciones_ssl=array(
				// 		"ssl"=>array(
				// 		"verify_peer"=>false,
				// 		"verify_peer_name"=>false,
				// 		),
				// 	);
				// 	CustomHelper::addNewPrinterCounter($po->getTable(),$po->id);
				// 	$img_path = 'website/logo_web_fix.png';
				// 	$extencion = pathinfo($img_path, PATHINFO_EXTENSION);
				// 	$image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
				// 	$img_base_64 = base64_encode($image_temp);
				// 	$path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
				// 	$data["image"]=$path_img;
				// 	$pdf = Pdf::loadView('admin.print.purchase.order_individual', $data)->setPaper('a4', 'portrait');
				// 	$font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
				// 	$pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $po->printCounter()->count(), $font, 10, array(0,0,0));
				// 	$pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));


				// 	$content = $pdf->download()->getOriginalContent();

				// 	$randomString = Str::random(10);


				// 	$filePath = 'public/pdf/' . $randomString . '.pdf';


				// 	Storage::put($filePath, $content);
				// 	$document_po = asset(Storage::url($filePath));
				// 	$fullPath = storage_path('app/' . $filePath);
				// 	$data = [
				// 		'subject' 	=> 'Dokumen Purchase Order',
				// 		'view' 		=> 'admin.mail.po_done',
				// 		'result' 	=> $po,
				// 		'supplier' 	=> $po->account->name,
				// 		'user' 		=> $po->user,
				// 		'company' 	=> $po->user->company,
				// 		'attachmentPath' => $fullPath,
				// 		'attachmentName' => 'attachment.pdf', // Adjust attachment name
				// 	];
				// 	try {
				// 		Mail::to($po->account->email)->send(new SendMail($data));

				// 	} catch (\Exception $e) {

				// 		Log::error('Error sending email: ' . $e->getMessage());
				// 		throw $e;
				// 	}
				// 	HistoryEmailDocument::create([
				// 		'user_id'		=> $po->user_id,
				// 		'account_id'	=> $po->account_id,
				// 		'lookable_type'	=> $table_name,
				// 		'lookable_id'	=> $table_id,
				// 		'status'		=> 1,
				// 		'email'			=> $po->account->email ?? '-',
				// 		'note'			=> $po->note,
				// 	]);
				// }

				$po->updateRootDocumentStatusDone();
			}
		}elseif($table_name == 'purchase_requests'){
			$pr = PurchaseRequest::find($table_id);

			if($pr){
				$pr->updateRootDocumentStatusDone();
			}
		}elseif($table_name == 'journals'){
			$je = Journal::find($table_id)->update(['status' => '3']);
		}elseif($table_name == 'production_schedules'){
			$ps = ProductionSchedule::find($table_id);
			if($ps){
				if($ps->productionScheduleTarget()->exists()){
					foreach($ps->productionScheduleTarget as $row){
						$row->marketingOrderPlanDetail->marketingOrderPlan->update([
							'status'	=> '3'
						]);
					}
				}
			}
		}elseif($table_name == 'production_recalculates'){
			$cj = ProductionRecalculate::find($table_id);

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

				$coaayatsilangstock = Coa::where('code','100.01.01.99.03')->where('company_id',$cj->company_id)->first();

				foreach($cj->productionRecalculateDetail as $row){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->resource->coa_id,
						'type'			=> $row->total > 0 ? '1' : '2',
						'nominal'		=> abs($row->total),
						'nominal_fc'	=> abs($row->total),
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
						'note'			=> 'REKALKULASI PRODUKSI NO. '.$cj->code,
					]);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coaayatsilangstock->id,
						'type'			=> $row->total < 0 ? '1' : '2',
						'nominal'		=> abs($row->total),
						'nominal_fc'	=> abs($row->total),
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'detailable_type'=> $row->getTable(),
						'detailable_id'	=> $row->id,
						'note'			=> 'REKALKULASI PRODUKSI NO. '.$cj->code,
					]);
				}

				$cj->update([
					'status'	=> '3'
				]);
			}
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

	public static function cancelJournal($data = null, $date = null){
		if($data->lookable_type == 'purchase_down_payments'){
			$pdp = PurchaseDownPayment::find($data->lookable_id);

			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pdp->company_id)->first();
			$coauangmuka = Coa::where('code','100.01.07.01.01')->where('company_id',$pdp->company_id)->first();

			$currency_rate = $pdp->latestCurrencyRateByDate($date);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pdp->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($date)).'00'),
				'lookable_type'	=> $data->getTable(),
				'lookable_id'	=> $data->id,
				'currency_id'	=> $pdp->currency_id,
				'currency_rate'	=> $currency_rate,
				'post_date'		=> $date,
				'note'			=> 'VOID CANCEL '.$pdp->code,
				'status'		=> '3'
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coahutangusaha->id,
				'account_id'	=> $coahutangusaha->bp_journal ? $pdp->account_id : NULL,
				'type'			=> '1',
				'nominal'		=> round($pdp->grandtotal * $currency_rate,2),
				'nominal_fc'	=> $pdp->currency->type == '1' ? round($pdp->grandtotal * $currency_rate,2) : $pdp->grandtotal,
				'note'			=> $pdp->code
			]);

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $coauangmuka->id,
				'account_id'	=> $coauangmuka->bp_journal ? $pdp->account_id : NULL,
				'type'			=> '2',
				'nominal'		=> round($pdp->grandtotal * $currency_rate,2),
				'nominal_fc'	=> $pdp->currency->type == '1' ? round($pdp->grandtotal * $currency_rate,2) : $pdp->grandtotal,
				'note'			=> $pdp->code
			]);
		}elseif($data->lookable_type == 'purchase_invoices'){
			$pi = PurchaseInvoice::find($data->lookable_id);

			$account_id = $pi->account_id;

			$currency_rate_invoice = $pi->latestCurrencyRateByDate($date);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'company_id'	=> $pi->company_id,
				'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($data->post_date)).'00'),
				'lookable_type'	=> $data->getTable(),
				'lookable_id'	=> $data->id,
				'currency_id'	=> $pi->currency_id,
				'currency_rate'	=> $currency_rate_invoice,
				'post_date'		=> $data->post_date,
				'note'			=> 'VOID CANCEL '.$pi->code,
				'status'		=> '3'
			]);

			$coauangmukapembelian = Coa::where('code','100.01.07.01.01')->where('company_id',$pi->company_id)->first();
			$coahutangbelumditagih = Coa::where('code','200.01.03.01.02')->where('company_id',$pi->company_id)->first();
			$coahutangusaha = Coa::where('code','200.01.03.01.01')->where('company_id',$pi->company_id)->first();
			$coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$pi->company_id)->first();
			$coaselisihkurs = Coa::where('code','700.01.01.01.02')->where('company_id',$pi->company_id)->first();

			$grandtotal = 0;
			$tax = 0;
			$wtax = 0;
			$realInvoice = 0;
			$realDownPayment = 0;

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
						'type'			=> '2',
						'nominal'		=> $row->total * $currency_rate_invoice,
						'nominal_fc'	=> $row->total,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
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
							'type'			=> '2',
							'nominal'		=> $row->tax * $currency_rate_invoice,
							'nominal_fc'	=> $row->tax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
							'type'			=> '1',
							'nominal'		=> $row->wtax * $currency_rate_invoice,
							'nominal_fc'	=> $row->wtax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
						'type'			=> '1',
						'nominal'		=> $row->grandtotal * $currency_rate_invoice,
						'nominal_fc'	=> $row->grandtotal,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);

				}elseif($row->lookable_type == 'purchase_order_details'){
					$type = $pi->currency->type;
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
						'type'			=> '2',
						'nominal'		=> $pod->getArrayTotal()['total'] * $currency_rate_invoice,
						'nominal_fc'	=> $type == '1' || $type == '' ? $pod->getArrayTotal()['total'] * $currency_rate_invoice : $pod->getArrayTotal()['total'],
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);

					$grandtotal += $row->grandtotal * $currency_rate_invoice;
					$tax += $row->tax * $currency_rate_invoice;
					$wtax += $row->wtax * $currency_rate_invoice;

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
							'type'			=> '2',
							'nominal'		=> $row->tax * $currency_rate_invoice,
							'nominal_fc'	=> $pi->currency->type == '1' ? $row->tax * $currency_rate_invoice : $row->tax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
							'type'			=> '1',
							'nominal'		=> $row->wtax * $currency_rate_invoice,
							'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->wtax * $currency_rate_invoice : $row->wtax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
						'type'			=> '1',
						'nominal'		=> $row->grandtotal * $currency_rate_invoice,
						'nominal_fc'	=> $pi->currency->type == '1' || $pi->currency->type == '' ? $row->grandtotal * $currency_rate_invoice : $row->grandtotal,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);

				}elseif($row->lookable_type == 'landed_cost_fee_details'){
					$type = $pi->currency->type;
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->lookable->landedCostFee->coa_id,
						'account_id'	=> $row->lookable->landedCostFee->coa->bp_journal ? $row->lookable->landedCost->account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->lookable->total * $currency_rate_invoice,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->lookable->total * $currency_rate_invoice : $row->lookable->total,
						'note'			=> 'VOID CANCEL '.$row->lookable->landedCostFee->name,
					]);

					$grandtotal += $row->grandtotal * $currency_rate_invoice;
					$tax += $row->tax * $currency_rate_invoice;
					$wtax += $row->wtax * $currency_rate_invoice;

					if($row->tax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->taxMaster->coa_purchase_id,
							'account_id'	=> $row->taxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '2',
							'nominal'		=> $row->tax * $currency_rate_invoice,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $currency_rate_invoice : $row->tax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
						]);
					}

					if($row->wtax_id){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->wTaxMaster->coa_purchase_id,
							'account_id'	=> $row->wTaxMaster->coaPurchase->bp_journal ? $account_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row->wtax * $currency_rate_invoice,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->wtax * $currency_rate_invoice : $row->wtax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : '',
						]);
					}

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->grandtotal * $currency_rate_invoice,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $currency_rate_invoice : $row->grandtotal,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);
				}else{
					$type = $pi->currency->type;

					$currency_rate = $row->lookable->goodReceipt->latestCurrencyRateByDate($pi->post_date);

					$totalgrpo = $row->total * $currency_rate;
					$totalinvoice = $row->total * $currency_rate_invoice;
					$balancegrpo = $totalgrpo - $totalinvoice;

					if($balancegrpo > 0 || $balancegrpo < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $coaselisihkurs->id,
							'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
							'type'			=> $balancegrpo > 0  ? '1' : '2',
							'nominal'		=> floatval(abs($balancegrpo)),
							'nominal_fc'	=> 0,
						]);
					}

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangbelumditagih->id,
						'place_id'		=> $row->place_id ? $row->place_id : NULL,
						'line_id'		=> $row->line_id ? $row->line_id : NULL,
						'machine_id'	=> $row->machine_id ? $row->machine_id : NULL,
						'account_id'	=> $coahutangbelumditagih->bp_journal ? $account_id : NULL,
						'department_id'	=> $row->department_id ? $row->department_id : NULL,
						'project_id'	=> $row->lookable->purchaseOrderDetail->project_id ? $row->lookable->purchaseOrderDetail->project_id : NULL,
						'type'			=> '2',
						'nominal'		=> $totalgrpo,
						'nominal_fc'	=> $type == '1' || $type == '' ? $totalgrpo : $row->total,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);

					$grandtotal += $row->grandtotal * $currency_rate_invoice;
					$tax += $row->tax * $currency_rate_invoice;
					$wtax += $row->wtax * $currency_rate_invoice;
					$currency_rate = $currency_rate_invoice;

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
							'type'			=> '2',
							'nominal'		=> $row->tax * $currency_rate_invoice,
							'nominal_fc'	=> $type == '1' || $type == '' ? $row->tax * $currency_rate_invoice : $row->tax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_no ? $row->purchaseInvoice->tax_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
							'type'			=> '1',
							'nominal'		=> $row->wtax * $currency_rate_invoice,
							'nominal_fc'	=> $type == '1' || $type == '2' ? $row->wtax * $currency_rate_invoice : $row->wtax,
							'note'			=> 'VOID CANCEL '.$row->purchaseInvoice->tax_cut_no ? $row->purchaseInvoice->tax_cut_no : '',
							'note2'			=> 'VOID CANCEL '.$row->purchaseInvoice->cut_date ? date('d/m/Y',strtotime($row->purchaseInvoice->cut_date)) : ''
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
						'type'			=> '1',
						'nominal'		=> $row->grandtotal * $currency_rate_invoice,
						'nominal_fc'	=> $type == '1' || $type == '' ? $row->grandtotal * $currency_rate_invoice : $row->grandtotal,
						'note'			=> 'VOID CANCEL '.$row->note,
						'note2'			=> 'VOID CANCEL '.$row->note2,
					]);
				}
			}

			#start journal rounding
			if($pi->rounding > 0 || $pi->rounding < 0){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coarounding->id,
					'account_id'	=> $coarounding->bp_journal ? $account_id : NULL,
					'type'			=> $pi->rounding > 0 ? '2' : '1',
					'nominal'		=> abs($pi->rounding * $currency_rate_invoice),
					'nominal_fc'	=> $type == '1' || $type == '' ? abs($pi->rounding * $currency_rate_invoice) : abs($pi->rounding),
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $coahutangusaha->id,
					'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
					'type'			=> $pi->rounding > 0 ? '1' : '2',
					'nominal'		=> abs($pi->rounding * $currency_rate_invoice),
					'nominal_fc'	=> $type == '1' || $type == '' ? abs($pi->rounding * $currency_rate_invoice) : abs($pi->rounding),
				]);
			}

			#start journal down payment

			if($pi->downpayment > 0){
				foreach($pi->purchaseInvoiceDp as $row){
					/* $downpayment += $row->nominal * $row->purchaseDownPayment->currency_rate; */
					$currencydp = $row->purchaseDownPayment->latestCurrencyRateByDate($pi->post_date);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coahutangusaha->bp_journal ? $account_id : NULL,
						'type'			=> '2',
						'nominal'		=> $row->nominal * $currencydp,
						'nominal_fc'	=> $row->purchaseDownPayment->currency->type == '1' || $row->purchaseDownPayment->currency->type == '' ? $row->nominal * $currencydp : $row->nominal,
					]);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coauangmukapembelian->id,
						'account_id'	=> $coauangmukapembelian->bp_journal ? $account_id : NULL,
						'type'			=> '1',
						'nominal'		=> $row->nominal * $currencydp,
						'nominal_fc'	=> $row->purchaseDownPayment->currency->type == '1' || $row->purchaseDownPayment->currency->type == '' ? $row->nominal * $currencydp : $row->nominal,
					]);
					$realDownPayment += $row->nominal * $currencydp;
					$realInvoice += $row->nominal * $currency_rate_invoice;

					if($row->purchaseDownPayment->balanceInvoice() <= 0){
						foreach($row->purchaseDownPayment->purchaseDownPaymentDetail as $rowdpdetail){
							if($rowdpdetail->fundRequestDetail()->exists()){
								$rowdpdetail->fundRequestDetail->fundRequest->update([
									'balance_status'	=> NULL
								]);
							}
						}
					}
				}

				$balanceKurs = round($realDownPayment - $realInvoice,2);

				if($balanceKurs > 0 || $balanceKurs < 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coaselisihkurs->id,
						'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
						'type'			=> $balanceKurs > 0  ? '2' : '1',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
					]);
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $coahutangusaha->id,
						'account_id'	=> $coaselisihkurs->bp_journal ? $pi->account_id : NULL,
						'type'			=> $balanceKurs > 0  ? '1' : '2',
						'nominal'		=> floatval(abs($balanceKurs)),
						'nominal_fc'	=> 0,
					]);
				}
			}

			$pi->updateRootDocumentStatusProcess();
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
			$row->approvalMatrix()->delete();
			$row->delete();
		}
	}

	public static function removeCogs($table_name = null, $table_id = null){
		$data = ItemCogs::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();

		if($data){
			foreach($data as $row){
				$company_id = $row->company_id;
				$item_id = $row->item_id;
				$place_id = $row->place_id;
				$warehouse_id = $row->warehouse_id;
				$area_id = $row->area_id ? $row->area_id : NULL;
				$item_shading_id = $row->item_shading_id ? $row->item_shading_id : NULL;
				$production_batch_id = $row->production_batch_id ? $row->production_batch_id : NULL;
				$qty = $row->qty_in ? $row->qty_in : $row->qty_out;
				$type = $row->qty_in ? 'IN' : 'OUT';
				$date = $row->date;
				self::resetStock($place_id,$warehouse_id,$area_id,$item_id,$item_shading_id,$production_batch_id,$qty,$type);
			}

			foreach($data as $row){
				$company_id = $row->company_id;
				$item_id = $row->item_id;
				$place_id = $row->place_id;
				$warehouse_id = $row->warehouse_id;
				$area_id = $row->area_id ? $row->area_id : NULL;
				$item_shading_id = $row->item_shading_id ? $row->item_shading_id : NULL;
				$production_batch_id = $row->production_batch_id ? $row->production_batch_id : NULL;
				$qty = $row->qty_in ? $row->qty_in : $row->qty_out;
				$type = $row->qty_in ? 'IN' : 'OUT';
				$date = $row->date;
				$detailable_type = $row->detailable_type;
				$detailable_id = $row->detailable_id;
				$row->delete();
				/* ResetCogsHelper::gas($date,$company_id,$place_id,$item_id,$area_id,$item_shading_id,$production_batch_id); */
				ResetCogsNew::dispatch($date,$company_id,$place_id,$item_id,$area_id,$item_shading_id,$production_batch_id,$detailable_type,$detailable_id);
			}
		}
	}

	public static function resetStock($place_id,$warehouse_id,$area_id,$item_id,$shading,$batch,$qty,$type){
		$data = ItemStock::where('place_id',$place_id)->where('warehouse_id',$warehouse_id)->where('area_id',$area_id)->where('item_id',$item_id)->where('item_shading_id',$shading)->where('production_batch_id',$batch)->first();

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
				'production_batch_id'=> $batch,
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

		$baca = array("nol", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");

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
		$arr = explode('.',$qty);
		$value = 0;
		if(count($arr) > 1){
			$trimmed_number = rtrim((string)$arr[1], '0');
			$trimmed_length = strlen($trimmed_number);
			if($trimmed_length > 3){
				$value = number_format(floatval($arr[0].'.'.$arr[1]),3,',','.');
			}else{
				$value = number_format(floatval($arr[0].'.'.$arr[1]),$trimmed_length,',','.');
			}
		}else{
			$value = number_format(floatval($arr[0]),0,',','.');
		}

		return $value;
	}

	public static function formatConditionalQtyFc($qty){
		$arr = explode('.',$qty);
		$value = 0;
		if(count($arr) > 1){
			$trimmed_number = rtrim((string)$arr[1], '0');
			$trimmed_length = strlen($trimmed_number);
			if($trimmed_length > 3){
				$value = number_format(floatval($arr[0].'.'.$arr[1]),11,',','.');
			}else{
				$value = number_format(floatval($arr[0].'.'.$arr[1]),$trimmed_length,',','.');
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

	public static function updateStatus($table_name = null, $table_id = null,$status = null){
		DB::table($table_name)->where('id',$table_id)->update([
			'status'	=> $status,
		]);
	}

	public static function compress($source, $destination, $quality) {

		$info = getimagesize($source);

		if ($info['mime'] == 'image/jpeg')
			$image = imagecreatefromjpeg($source);

		elseif ($info['mime'] == 'image/gif')
			$image = imagecreatefromgif($source);

		elseif ($info['mime'] == 'image/png')
			$image = imagecreatefrompng($source);

		imagejpeg($image, $destination, $quality);

		return $destination;
	}

	public static function randomColor($minVal,$maxVal){
		$minVal = $minVal < 0 || $minVal > 255 ? 0 : $minVal;
		$maxVal = $maxVal < 0 || $maxVal > 255 ? 255 : $maxVal;

		// Generate 3 values
		$r = mt_rand($minVal, $maxVal);
		$g = mt_rand($minVal, $maxVal);
		$b = mt_rand($minVal, $maxVal);

		// Return a hex colour ID string
		return sprintf('#%02X%02X%02X', $r, $g, $b);
	}

	public static function updateProductionBatch($id,$qty,$type){
		$data = ProductionBatch::find($id);
		if($data){
			if($type == 'IN'){
				$data->update([
					'qty'	=> $data->qty + round($qty,3)
				]);
			}elseif($type == 'OUT'){
				$data->update([
					'qty'	=> $data->qty - round($qty,3)
				]);
			}
		}
	}

	public static function countDays($start_date, $end_date){
		$date1 = strtotime($start_date);
		$date2 = strtotime($end_date);
		$diff = $date2 - $date1;
		$days = floor($diff / (60 * 60 * 24));
		return $days;
	}

	public static function accumulateCogs($date,$company_id,$place_id,$item_id){
		$item = Item::find($item_id);
        $bomPowder = $item->bomPlace($place_id) ? $item->bomPlace($place_id)->first() : NULL;
        $bomGroup = '';
        if($bomPowder){
            $bomGroup = $bomPowder->group;
        }
		if($bomGroup == '2' || $bomGroup == '3'){
            $itemcogs2 = ItemCogs::where('date','>=',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderBy('date')->orderBy('id')->get();
            $old_data2 = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderByDesc('date')->orderByDesc('id')->first();

            $total_final = 0;
            $qty_final = 0;
            $price_final = 0;
            foreach($itemcogs2 as $key2 => $row){
                if($key2 == 0){
                    if($old_data2){
                        if($row->type == 'IN'){
                            $total_final = $old_data2->total_final + $row->total_in;
                            $qty_final = $old_data2->qty_final + $row->qty_in;
                        }elseif($row->type == 'OUT'){
                            $total_final = $old_data2->total_final - $row->total_out;
                            $qty_final = $old_data2->qty_final - $row->qty_out;
                        }

                        $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    }else{
                        if($row->type == 'IN'){
                            $total_final = $row->total_in;
                            $qty_final = $row->qty_in;
                        }elseif($row->type == 'OUT'){
                            $total_final = 0 - $row->total_out;
                            $qty_final = 0 - $row->qty_out;
                        }

                        $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    }
                    $row->update([
                        'price_final'	=> $price_final,
                        'qty_final'		=> round($qty_final,3),
                        'total_final'	=> round($total_final,2),
                    ]);
                }else{
                    if($row->type == 'IN'){
                        $total_final += $row->total_in;
                        $qty_final += $row->qty_in;
                    }elseif($row->type == 'OUT'){
                        $total_final -= $row->total_out;
                        $qty_final -= $row->qty_out;
                    }
                    $price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
                    $row->update([
                        'price_final'	=> $price_final,
                        'qty_final'		=> round($qty_final,3),
                        'total_final'	=> round($total_final,2),
                    ]);
                }
            }
        }
	}

	public static function accumulateCogsForAll($date,$company_id,$place_id,$item_id){
		$itemcogs2 = ItemCogs::where('date','>=',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderBy('date')->orderBy('id')->get();
		$old_data2 = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderByDesc('date')->orderByDesc('id')->first();

		$total_final = 0;
		$qty_final = 0;
		$price_final = 0;
		foreach($itemcogs2 as $key2 => $row){
			if($key2 == 0){
				if($old_data2){
					if($row->type == 'IN'){
						$total_final = $old_data2->total_final + $row->total_in;
						$qty_final = $old_data2->qty_final + $row->qty_in;
					}elseif($row->type == 'OUT'){
						$total_final = $old_data2->total_final - $row->total_out;
						$qty_final = $old_data2->qty_final - $row->qty_out;
					}

					$price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
				}else{
					if($row->type == 'IN'){
						$total_final = $row->total_in;
						$qty_final = $row->qty_in;
					}elseif($row->type == 'OUT'){
						$total_final = 0 - $row->total_out;
						$qty_final = 0 - $row->qty_out;
					}

					$price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
				}
				$row->update([
					'price_final'	=> $price_final,
					'qty_final'		=> round($qty_final,3),
					'total_final'	=> round($total_final,2),
				]);
			}else{
				if($row->type == 'IN'){
					$total_final += $row->total_in;
					$qty_final += $row->qty_in;
				}elseif($row->type == 'OUT'){
					$total_final -= $row->total_out;
					$qty_final -= $row->qty_out;
				}
				$price_final = $qty_final > 0 ? round($total_final / $qty_final,5) : 0;
				$row->update([
					'price_final'	=> $price_final,
					'qty_final'		=> round($qty_final,3),
					'total_final'	=> round($total_final,2),
				]);
			}
		}
	}

	public static function splitBomArray($data){
		$newarray = [];
		$arritem = [];
		$datanew = [];
		foreach($data as $row){
			foreach($row['list_bom'] as $rowbom){
				$newarray[] = $rowbom;
			}
		}
		$item_id = NULL;
		foreach($newarray as $row){
			if(!in_array($row['item_id'],$arritem)){
				$arritem[] = $row['item_id'];
				$arrqty = 0;
			}else{
				$item_id = $row['item_id'];
			}
		}
		$newrow = NULL;
		if($item_id){
			$countQty = 0;
			foreach($data as $row){
				$newarr = [];
				foreach($row['list_bom'] as $key => $rowbom){
					if($rowbom['item_id'] !== $item_id){
						$newarr[] = $rowbom;
					}else{
						$countQty += str_replace(',','.',str_replace('.','',$row['qty'])) * str_replace(',','.',str_replace('.','',$rowbom['qty']));
						$arrnewbom[0] = $rowbom;
						$newrow = [
							'mopd_id' 			=> '',
							'item_id' 			=> $item_id,
							'item_code' 		=> explode(' - ',$rowbom['item_name'])[0],
							'item_name' 		=> explode(' - ',$rowbom['item_name'])[1],
							'qty' 				=> CustomHelper::formatConditionalQty($countQty),
							'uom' 				=> $rowbom['unit'],
							'request_date' 		=> $row['request_date'],
							'note' 				=> '-',
							'note2' 			=> '-',
							'priority' 			=> NULL,
							'has_bom' 			=> '1',
							'place_id' 			=> '1',
							'list_warehouse'	=> $rowbom['list_warehouse'],
							'list_bom'			=> $arrnewbom,
						];
					}
				}
				$row['list_bom'] = $newarr;
				$datanew[] = $row;
			}
		}else{
			$datanew = $data;
		}
		if($newrow){
			$datanew[] = $newrow;
		}
		return $datanew;
	}
}
