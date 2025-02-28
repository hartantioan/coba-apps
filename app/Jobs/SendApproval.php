<?php

namespace App\Jobs;

use App\Exports\ExportStockMovement;
use App\Helpers\CustomHelper;
use App\Helpers\WaBlas;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\ApprovalTemplate;
use App\Models\GoodIssueRequest;
use App\Models\GoodScale;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MaterialRequest;
use App\Models\Notification;
use App\Models\ProductionSchedule;
use App\Models\UserBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class SendApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $table_name, $table_id, $note, $user_id;

    public function __construct(string $table_name = NULL, int $table_id = NULL, string $note = NULL, int $user_id = NULL)
    {
        $this->table_name = $table_name;
        $this->table_id = $table_id;
        $this->queue = 'approval';
        $this->user_id = $user_id;
		$this->note = $note;
    }

    public function handle()
    {
        $table_name = $this->table_name;
        $table_id = $this->table_id;
        $note = $this->note;

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
			$query->where('user_id',$this->user_id);
		})->get();

		$underEbitda = false;

		if($table_name == 'marketing_orders'){
			$salesOrder = MarketingOrder::find($table_id);
			if($salesOrder){
				$underEbitda = $salesOrder->underEbitda();
			}
		}

		$daysDueInvoiceMod = 0;

		if($table_name == 'marketing_order_deliveries'){
			$mod = MarketingOrderDelivery::find($table_id);
			if($mod){
				$daysDueInvoiceMod = $mod->invoiceDueDate();
			}
		}

		$isGoodScale = false;

		if($table_name == 'good_scales'){
			$gs = GoodScale::find($table_id);
			if($gs){
				if($gs->qty_balance == 0){
					$isGoodScale = true;
				}else{
					if($gs->goodScaleDetail()->where('lookable_type','marketing_order_deliveries')->count() > 0){
						$hasOverToleranceGs = false;
						foreach($gs->goodScaleDetail->where('lookable_type','marketing_order_deliveries') as $row){
							if($row->lookable->hasOverToleranceGoodScale()){
								$hasOverToleranceGs = true;
							}
						}
						if(!$hasOverToleranceGs){
							$isGoodScale = true;
						}
					}
				}
			}
		}

		$count = 0;

		$currency_rate = isset($data->currency_rate) ? $data->currency_rate : 1;

		foreach($approvalTemplate as $row){

			$source = ApprovalSource::create([
				'code'			=> strtoupper(uniqid()),
				'user_id'		=> $this->user_id,
				'date_request'	=> date('Y-m-d H:i:s'),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'note'			=> $note,
			]);

			$passed = $isGoodScale ? false : true;

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
							if(!CustomHelper::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
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
							if(!CustomHelper::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}else{
						#checknominal dan bukanrange
						if(!CustomHelper::compare($data->grandtotal * $currency_rate,$row->sign,$row->nominal)){
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
							if(!CustomHelper::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
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
							if(!CustomHelper::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
								$passed = false;
							}
						}else{
							$passed = false;
						}
					}else{
						#checknominal dan range
						if(!CustomHelper::compareRange($data->grandtotal * $currency_rate,$row->nominal,$row->nominal_final)){
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
							if(!CustomHelper::compare($percentDiff,$row->sign,$row->nominal)){
								$passed = false;
							}
						}else{
							if(!CustomHelper::compareRange($percentDiff,$row->nominal,$row->nominal_final)){
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
					$check = true;
					if($table_name == 'marketing_orders' && $rowTemplateStage->approvalStage->level == 2){
						if(!$underEbitda){
							$check = false;
						}
					}
					if($table_name == 'marketing_order_deliveries' && $daysDueInvoiceMod > 0){
						if($daysDueInvoiceMod <= 7 && $rowTemplateStage->approvalStage->level == 2){
							$check = false;
						}
						if($daysDueInvoiceMod <= 15 && $rowTemplateStage->approvalStage->level == 3){
							$check = false;
						}
					}
					if($table_name == 'marketing_order_deliveries' && $daysDueInvoiceMod == 0){
						$check = false;
					}
					if($check){
						foreach($rowTemplateStage->approvalStage->approvalStageDetail as $rowStageDetail){
							$checkPerUser = true;
							if($table_name == 'marketing_order_deliveries' && $daysDueInvoiceMod > 0 && $rowTemplateStage->approvalStage->level == 1){
								if(!$source->lookable->customer->brand()->exists()){
									$countRow = UserBrand::where('account_id',$rowStageDetail->user_id)->count();
									if($countRow > 0){
										$checkPerUser = false;
									}
								}else{
									$countRow = UserBrand::where('account_id',$rowStageDetail->user_id)->where('brand_id',$source->lookable->customer->brand->id)->count();
									if($countRow == 0 && $rowStageDetail->user_id !== 934){
										$checkPerUser = false;
									}
								}
							}
							if($checkPerUser){
								ApprovalMatrix::create([
									'code'							=> strtoupper(Str::random(30)),
									'approval_template_stage_id'	=> $rowTemplateStage->id,
									'approval_source_id'			=> $source->id,
									'user_id'						=> $rowStageDetail->user_id,
									'date_request'					=> date('Y-m-d H:i:s'),
									'status'						=> $status
								]);
								if(in_array($rowStageDetail->user->phone,['085729547103','087788809205']) && $status == '1'){
									WaBlas::kirim_wa($rowStageDetail->user->phone,'Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
									WaBlas::kirim_wa('081330074432','Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
								}
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
				SendJournal::dispatch($table_name,$table_id,$data->account_id,$this->user_id);
			}else{
				SendJournal::dispatch($table_name,$table_id,null,$this->user_id);
			}
		}
    }
}
