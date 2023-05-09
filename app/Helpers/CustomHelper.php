<?php 

namespace App\Helpers;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalStage;
use App\Models\ApprovalSource;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateMenu;
use App\Models\Asset;
use App\Models\Capitalization;
use App\Models\Coa;
use App\Models\Depreciation;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceiptMain;
use App\Models\GoodReceive;
use App\Models\InventoryTransfer;
use App\Models\Item;
use App\Models\ItemGroupWarehouse;
use App\Models\OutgoingPayment;
use App\Models\Place;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\Retirement;
use App\Models\User;
use App\Models\Notification;
use App\Models\Menu;
use App\Models\MenuCoa;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\LandedCost;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Models\UsedData;
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

	public static function sendCogs($lookable_type = null, $lookable_id = null, $company_id = null, $place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $total = null, $type = null, $date = null){
		$old_data = ItemCogs::where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderByDesc('date')->orderByDesc('id')->first();
		if($type == 'IN'){
			ItemCogs::create([
				'lookable_type'	=> $lookable_type,
				'lookable_id'	=> $lookable_id,
				'company_id'	=> $company_id,
				'place_id'		=> $place_id,
				'warehouse_id'	=> $warehouse_id,
				'item_id'		=> $item_id,
				'qty_in'		=> $qty,
				'price_in'		=> $total / $qty,
				'total_in'		=> $total,
				'qty_final'		=> $old_data ? $old_data->qty_final + $qty : $qty,
				'price_final'	=> $old_data ? round((($old_data->total_final + $total) / ($old_data->qty_final + $qty)),3) : round($total / $qty,3),
				'total_final'	=> $old_data ? round(($old_data->total_final + $total),3) : $total,
				'date'			=> $date,
				'type'			=> $type
			]);
		}elseif($type == 'OUT'){
			if($old_data){
				$priceeach = $old_data->price_final;
				$totalout = round($priceeach * $qty,3);
				$qtybalance = $old_data->qty_final - $qty;
				$totalfinal = round($qtybalance * $priceeach,3);
				ItemCogs::create([
					'lookable_type'	=> $lookable_type,
					'lookable_id'	=> $lookable_id,
					'company_id'	=> $company_id,
					'place_id'		=> $place_id,
					'warehouse_id'	=> $warehouse_id,
					'item_id'		=> $item_id,
					'qty_out'		=> $qty,
					'price_out'		=> $priceeach,
					'total_out'		=> $totalout,
					'qty_final'		=> $qtybalance,
					'price_final'	=> $priceeach,
					'total_final'	=> $totalfinal,
					'date'			=> $date,
					'type'			=> $type
				]);
			}
		}
	}

	public static function sendStock($place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $type = null){
		$old_data = ItemStock::where('place_id',$place_id)->where('item_id',$item_id)->where('warehouse_id',$warehouse_id)->first();
		if($old_data){
			$old_data->update([
				'qty' => $type == 'IN' ? $old_data->qty + $qty : $old_data->qty - $qty,
			]);
		}else{
			ItemStock::create([
				'place_id'		=> $place_id,
				'warehouse_id'	=> $warehouse_id,
				'item_id'		=> $item_id,
				'qty'			=> $type == 'IN' ? $qty : 0 - $qty,
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

	public static function sendApproval($table_name,$table_id,$note){
		$resetdata = ApprovalSource::where('lookable_type',$table_name)->where('lookable_id',$table_id)->get();

		foreach($resetdata as $rowreset){
			foreach($rowreset->approvalMatrix as $detailmatrix){
				$detailmatrix->delete();
			}
			$rowreset->delete();
		}

		$data = DB::table($table_name)->where('id',$table_id)->first();

		$source = ApprovalSource::create([
			'code'			=> strtoupper(uniqid()),
			'user_id'		=> session('bo_id'),
			'date_request'	=> date('Y-m-d H:i:s'),
			'lookable_type'	=> $table_name,
			'lookable_id'	=> $table_id,
			'note'			=> $note,
		]);

		$approvalTemplate = ApprovalTemplate::where('status','1')
		->whereHas('approvalTemplateMenu',function($query) use($table_name){
			$query->where('table_name',$table_name);
		})
		->whereHas('approvalTemplateOriginator',function($query){
			$query->where('user_id',session('bo_id'));
		})->get();
		info($approvalTemplate);
		
		$count = 0;

		foreach($approvalTemplate as $row){
			$passed = true;

			if($row->is_check_nominal){
				if(!self::compare($data->grandtotal,$row->sign,$row->nominal)){
					$passed = false;
				}
			}

			if($passed == true){

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
			$data = $source->lookable;
			
			$data->update([
				'status'	=> '2'
			]);

			self::sendJournal($table_name,$table_id,$data->account_id);
		}
	}

	public static function sendNotification($table_name = null, $table_id = null, $title = null, $note = null, $to = null){
		
		$menu = Menu::where('table_name',$table_name)->first();

		$arrUser = [];

		if($menu){
			foreach($menu->menuUser as $row){
				$arrUser[] = $row->user_id;
			}

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

	public static function sendJournal($table_name = null,$table_id = null,$account_id = null){

		$data = DB::table($table_name)->where('id',$table_id)->first();

		if($table_name == 'good_receipts'){

			$gr = GoodReceipt::find($table_id);

			$arrdata = json_decode(json_encode($gr), true);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> 'good_receipts',
				'lookable_id'	=> $gr->id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			$arrCoa = [];

			foreach($gr->goodReceiptDetail as $rowdetail){
				$rowtotal = $rowdetail->getRowTotal();

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
					'place_id'		=> $rowdetail->place_id,
					'account_id'	=> $gr->account_id,
					'department_id'	=> $rowdetail->department_id,
					'warehouse_id'	=> $rowdetail->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $rowtotal
				]);

				self::sendCogs('good_receipts',
					$gr->id,
					$gr->company_id,
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					$rowtotal,
					'IN',
					$gr->post_date
				);

				self::sendStock(
					$rowdetail->place_id,
					$rowdetail->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qtyConvert(),
					'IN'
				);

				$journalMap = MenuCoa::whereHas('menu', function($query){
					$query->where('table_name','good_receipts');
				})
				->whereHas('coa', function($query) use($data){
					$query->where('company_id',$data->company_id);
				})->get();

				foreach($journalMap as $row){
					$nominal = $rowtotal * ($row->percentage / 100);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->coa_id,
						'place_id'		=> $rowdetail->place_id,
						'account_id'	=> $gr->account_id,
						'department_id'	=> $rowdetail->department_id,
						'warehouse_id'	=> $rowdetail->warehouse_id,
						'type'			=> '2',
						'nominal'		=> $rowtotal
					]);
				}
			}

		}elseif($table_name == 'retirements'){
			$ret = Retirement::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'place_id'		=> $ret->place_id,
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
						'nominal'		=> $totalDepre,
					]);
				}

				if($row->asset->book_balance > 0 && $row->retirement_nominal == 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->coa_id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> $row->asset->book_balance,
					]);
				}

				if($row->retirement_nominal > 0){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->coa_id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> $row->retirement_nominal,
					]);

					$balanceProfitLoss = ($totalDepre + $row->retirement_nominal) - $row->asset->nominal;
					$coaProfitLoss = Coa::where('code','700.01.01.01.04')->where('status','1')->where('company_id',$row->asset->place->company_id)->first();

					if($coaProfitLoss){
						if($balanceProfitLoss > 0){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coaProfitLoss->id,
								'place_id'		=> $row->asset->place_id,
								'type'			=> '2',
								'nominal'		=> $balanceProfitLoss,
							]);
						}

						if($balanceProfitLoss < 0){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $coaProfitLoss->id,
								'place_id'		=> $row->asset->place_id,
								'type'			=> '1',
								'nominal'		=> abs($balanceProfitLoss),
							]);
						}
					}
				}

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '2',
					'nominal'		=> $row->asset->nominal,
				]);

				self::updateBalanceAsset($row->asset_id,$row->asset->book_balance,'OUT');
			}

		}elseif($table_name == 'outgoing_payments'){
			$op = OutgoingPayment::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> 'outgoing_payments',
				'lookable_id'	=> $op->id,
				'currency_id'	=> $op->currency_id,
				'currency_rate'	=> $op->currency_rate,
				'post_date'		=> $op->pay_date,
				'note'			=> $op->code,
				'status'		=> '3'
			]);

			foreach($op->paymentRequest->paymentRequestDetail as $row){
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->coa_id,
					'account_id'	=> $op->account_id,
					'type'			=> '1',
					'nominal'		=> $row->nominal,
				]);
			}

			$journalMap = MenuCoa::whereHas('menu', function($query) use ($table_name){
				$query->where('table_name',$table_name);
			})
			->whereHas('coa', function($query) use($data){
				$query->where('company_id',$data->company_id);
			})->get();

			if(count($journalMap) > 0){
				$arrdata = get_object_vars($data);

				foreach($journalMap as $row){
					$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

					if($nominal > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'account_id'	=> $op->account_id,
							'type'			=> $row->type,
							'nominal'		=> $nominal
						]);
					}
				}
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $op->coa_source_id,
				'account_id'	=> $op->account_id,
				'type'			=> '2',
				'nominal'		=> $op->grandtotal,
			]);

		}elseif($table_name == 'good_receives'){

			$gr = GoodReceive::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
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
					'department_id'	=> $row->department_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $row->total,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->coa_id,
					'place_id'		=> $row->place_id,
					'department_id'	=> $row->department_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '2',
					'nominal'		=> $row->total,
				]);

				self::sendCogs('good_receives',
					$gr->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total,
					'IN',
					$gr->post_date
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					'IN'
				);
			}

		}elseif($table_name == 'good_issues'){

			$gr = GoodIssue::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> 'good_issues',
				'lookable_id'	=> $gr->id,
				'post_date'		=> $gr->post_date,
				'note'			=> $gr->code,
				'status'		=> '3'
			]);

			foreach($gr->goodIssueDetail as $row){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->coa_id,
					'place_id'		=> $row->place_id,
					'department_id'	=> $row->department_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '1',
					'nominal'		=> $row->total,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->item->itemGroup->coa_id,
					'place_id'		=> $row->place_id,
					'department_id'	=> $row->department_id,
					'warehouse_id'	=> $row->warehouse_id,
					'type'			=> '2',
					'nominal'		=> $row->total,
				]);

				self::sendCogs('good_issues',
					$gr->id,
					$row->place->company_id,
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					$row->total,
					'OUT',
					$gr->post_date
				);

				self::sendStock(
					$row->place_id,
					$row->warehouse_id,
					$row->item_id,
					$row->qty,
					'OUT'
				);
			}
			
		}elseif($table_name == 'landed_costs'){

			$arrCoa = [];

			$lc = LandedCost::find($data->id);
			
			if($lc){
				$query = Journal::create([
					'user_id'		=> session('bo_id'),
					'code'			=> Journal::generateCode(),
					'lookable_type'	=> 'landed_costs',
					'lookable_id'	=> $lc->id,
					'post_date'		=> $data->post_date,
					'note'			=> $data->code,
					'status'		=> '3'
				]);

				foreach($lc->landedCostDetail as $rowdetail){
					$pricelc = $rowdetail->nominal / $rowdetail->qty;

					$pricenew = 0;
					$itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$lc->good_receipt_id)->where('place_id',$rowdetail->place_id)->where('item_id',$rowdetail->item_id)->first();
					if($itemdata){
						$pricenew = $pricelc + $itemdata->price_in;
						$itemdata->update([
							'price_in'	=> $pricenew,
							'total_in'	=> round($pricenew * $itemdata->qty_in,3),
						]);
					}

					self::resetCogsItem($rowdetail->place_id,$rowdetail->item_id);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
						'place_id'		=> $rowdetail->place_id,
						'account_id'	=> $lc->account_id,
						'department_id'	=> $rowdetail->department_id,
						'warehouse_id'	=> $rowdetail->warehouse_id,
						'type'			=> '1',
						'nominal'		=> $rowdetail->nominal
					]);

					$journalMap = MenuCoa::whereHas('menu', function($query){
						$query->where('table_name','landed_costs');
					})
					->whereHas('coa', function($query) use($data){
						$query->where('company_id',$data->company_id);
					})->get();
	
					foreach($journalMap as $row){
						$nominal = $rowdetail->nominal * ($row->percentage / 100);
						
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> $rowdetail->place_id,
							'account_id'	=> $lc->account_id,
							'department_id'	=> $rowdetail->department_id,
							'warehouse_id'	=> $rowdetail->warehouse_id,
							'type'			=> '2',
							'nominal'		=> $nominal,
						]);
					}
				}
			}
		}elseif($table_name == 'fund_requests'){
		
		}elseif($table_name == 'capitalizations'){		
			$arrdata = get_object_vars($data);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'currency_id'	=> $data->currency_id,
				'currency_rate'	=> $data->currency_rate,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);
			
			$cp = Capitalization::find($data->id);
			if($cp){
				foreach($cp->capitalizationDetail as $row){
					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> $row->asset->assetGroup->coa_id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '1',
						'nominal'		=> $row->total
					]);

					JournalDetail::create([
						'journal_id'	=> $query->id,
						'coa_id'		=> Coa::where('code','100.01.01.99.03')->where('company_id',$row->asset->place->company_id)->first()->id,
						'place_id'		=> $row->asset->place_id,
						'type'			=> '2',
						'nominal'		=> $row->total
					]);
				}
			}
		}elseif($table_name == 'inventory_transfers'){

			$it = InventoryTransfer::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			foreach($it->inventoryTransferDetail as $rowdetail){
				$priceout = $rowdetail->item->priceNow($rowdetail->itemStock->place_id);
				$nominal = $rowdetail->qty * $priceout;
				
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
					'place_id'		=> $rowdetail->to_place_id,
					'warehouse_id'	=> $rowdetail->to_warehouse_id,
					'type'			=> '1',
					'nominal'		=> $nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $rowdetail->item->itemGroup->coa_id,
					'place_id'		=> $rowdetail->itemStock->place_id,
					'warehouse_id'	=> $rowdetail->itemStock->warehouse_id,
					'type'			=> '2',
					'nominal'		=> $nominal,
				]);

				self::sendCogs('inventory_transfers',
					$it->id,
					$rowdetail->itemStock->place->company_id,
					$rowdetail->itemStock->place_id,
					$rowdetail->itemStock->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					$nominal,
					'OUT',
					$it->post_date
				);

				self::sendStock(
					$rowdetail->itemStock->place_id,
					$rowdetail->itemStock->warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					'OUT'
				);

				self::sendCogs('inventory_transfers',
					$it->id,
					$rowdetail->toPlace->company_id,
					$rowdetail->to_place_id,
					$rowdetail->to_warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					$nominal,
					'IN',
					$it->post_date
				);

				self::sendStock(
					$rowdetail->to_place_id,
					$rowdetail->to_warehouse_id,
					$rowdetail->item_id,
					$rowdetail->qty,
					'IN'
				);
			}
		}elseif($table_name == 'depreciations'){

			$dpr = Depreciation::find($table_id);

			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'lookable_type'	=> $table_name,
				'lookable_id'	=> $table_id,
				'post_date'		=> $data->post_date,
				'note'			=> $data->code,
				'status'		=> '3'
			]);

			foreach($dpr->depreciationDetail as $row){

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->cost_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '1',
					'nominal'		=> $row->nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->depreciation_coa_id,
					'place_id'		=> $row->asset->place_id,
					'type'			=> '2',
					'nominal'		=> $row->nominal,
				]);
				
				self::updateBalanceAsset($row->asset_id,$row->nominal,'OUT');
			}

		}else{

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
					'code'			=> Journal::generateCode(),
					'lookable_type'	=> $table_name,
					'lookable_id'	=> $table_id,
					'currency_id'	=> isset($data->currency_id) ? $data->currency_id : NULL,
					'currency_rate'	=> isset($data->currency_rate) ? $data->currency_rate : NULL,
					'post_date'		=> $data->post_date,
					'note'			=> $data->code,
					'status'		=> '3'
				]);

				#start untuk po tipe biaya / jasa
				$totalOutSide = 0;

				if($table_name == 'purchase_invoices'){
					$pi = PurchaseInvoice::find($table_id);

					foreach($pi->purchaseInvoiceDetail()->whereNotNull('purchase_order_id')->get() as $row){
						$po = PurchaseOrder::find($row->purchase_order_id);

						foreach($po->purchaseOrderDetail as $rowpo){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $rowpo->coa_id,
								'place_id'		=> $rowpo->place_id,
								'account_id'	=> $account_id,
								'department_id'	=> $rowpo->department_id,
								'type'			=> '1',
								'nominal'		=> $rowpo->subtotal
							]);
							
							$totalOutSide += $rowpo->subtotal;
						}
					}

					#start journal rounding
					if($pi->rounding > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> Coa::where('code','700.01.01.01.05')->where('company_id',$pi->company_id)->first()->id,
							'account_id'	=> $account_id,
							'type'			=> '1',
							'nominal'		=> abs($pi->rounding),
						]);
					}
					
					if($pi->rounding < 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> Coa::where('code','700.01.01.01.05')->where('company_id',$pi->company_id)->first()->id,
							'account_id'	=> $account_id,
							'type'			=> '2',
							'nominal'		=> abs($pi->rounding),
						]);
					}
					#end journal rounding
				}
				#end untuk po tipe biaya / jasa

				foreach($journalMap as $row){
					
					$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

					if($totalOutSide > 0){
						if($row->field_name == 'total'){
							$nominal -= $totalOutSide;
						}
					}

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
		}
	}

	public static function removeJournal($table_name = null, $table_id = null){
		$data = Journal::where('lookable_type',$table_name)->where('lookable_id',$table_id)->first();

		if($data){
			$data->journalDetail()->delete();
			$data->delete();
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
				$qty = $row->qty_in ? $row->qty_in : $row->qty_out;
				$type = $row->qty_in ? 'IN' : 'OUT';
				
				$row->delete();

				self::resetCogsItem($place_id,$item_id);

				self::resetStock($place_id,$warehouse_id,$item_id,$qty,$type);
			}
		}
	}

	public static function resetCogsItem($place_id = null, $item_id = null){
		$data = ItemCogs::where('place_id',$place_id)->where('item_id',$item_id)->orderBy('date')->orderBy('id')->get();

		foreach($data as $key => $row){
			if($key == 0){
				if($row->type == 'IN'){
					$finalprice = $row->total_in / $row->qty_in;
					$totalprice = $finalprice * $row->qty_in;
					$row->update([
						'qty_final' 	=> $row->qty_in,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}
			}else{
				$prevqty = $data[$key-1]->qty_final;
				$prevtotal = $data[$key-1]->total_final;
				if($row->type == 'IN'){
					$finalprice = ($prevtotal + $row->total_in) / ($prevqty + $row->qty_in);
					$totalprice = $finalprice * ($prevqty + $row->qty_in);
					$row->update([
						'qty_final' 	=> $prevqty + $row->qty_in,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}elseif($row->type == 'OUT'){
					$finalprice = ($prevtotal - $row->total_out) / ($prevqty - $row->qty_out);
					$totalprice = $finalprice * ($prevqty - $row->qty_out);
					$row->update([
						'qty_final' 	=> $prevqty - $row->qty_out,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}
			}
		}
	}

	public static function resetStock($place_id = null, $warehouse_id = null, $item_id = null, $qty = null, $type = null){
		$data = ItemStock::where('place_id',$place_id)->where('warehouse_id',$warehouse_id)->where('item_id',$item_id)->first();

		if($data){
			$data->update([
				'qty' => $type == 'IN' ? $data->qty - $qty : $data->qty + $qty,
			]);
		}else{
			ItemStock::create([
				'place_id'		=> $place_id,
				'warehouse_id'	=> $warehouse_id,
				'item_id'		=> $item_id,
				'qty'			=> $type == 'IN' ? 0 - $qty : $qty,
			]);
		}
	}

	public static function updateBalanceAsset($asset_id = null, $nominal = null, $type = null){
		$asset = Asset::find($asset_id);
		
		if($asset){
			$asset->update([
				'book_balance' => $type == 'OUT' ? round($asset->book_balance - $nominal,3) : round($asset->book_balance + $nominal,3),
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

	public static function terbilang($angka) {
		$angka=abs($angka);
		
		$baca =array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
	  
		$terbilang="";
		 if ($angka < 12){
			 $terbilang= " " . $baca[$angka];
		 }
		 else if ($angka < 20){
			 $terbilang= self::terbilang($angka - 10) . " belas";
		 }
		 else if ($angka < 100){
			 $terbilang= self::terbilang($angka / 10) . " puluh" . self::terbilang($angka % 10);
		 }
		 else if ($angka < 200){
			 $terbilang= " seratus" . self::terbilang($angka - 100);
		 }
		 else if ($angka < 1000){
			 $terbilang= self::terbilang($angka / 100) . " ratus" . self::terbilang($angka % 100);
		 }
		 else if ($angka < 2000){
			 $terbilang= " seribu" . self::terbilang($angka - 1000);
		 }
		 else if ($angka < 1000000){
			 $terbilang= self::terbilang($angka / 1000) . " ribu" . self::terbilang($angka % 1000);
		 }
		 else if ($angka < 1000000000){
			$terbilang= self::terbilang($angka / 1000000) . " juta" . self::terbilang($angka % 1000000);
		 }
		 else if ($angka < 1000000000000){
			$terbilang= self::terbilang($angka / 1000000000) . " miliar" . self::terbilang($angka % 1000000000);
		 }
		 
		 return ucwords($terbilang);
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

		$newItem = $item;

		$arrString = explode('-',$item->code);

		if(count($arrString) > 0){
			$lastIndex = count($arrString) - 1;
			if($arrString[$lastIndex] !== 'SVC'){
				$newItem = $item->replicate();
				$newItem->code = $item->code.'-SVC';
				$newItem->save();
			}
		}

		return $newItem->id;
	}
}