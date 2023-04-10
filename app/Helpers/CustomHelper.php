<?php 

namespace App\Helpers;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalTable;
use App\Models\ApprovalSource;
use App\Models\Capitalization;
use App\Models\Coa;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceiptMain;
use App\Models\OutgoingPayment;
use App\Models\Place;
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
				$priceeach = $old_data->total_final / $qty;
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
			'place_id'		=> $data->place_id,
			'date_request'	=> date('Y-m-d H:i:s'),
			'lookable_type'	=> $table_name,
			'lookable_id'	=> $table_id,
			'note'			=> $note,
		]);

		$approvaltable = ApprovalTable::where('table_name',$table_name)->where('status','1')->orderBy('level')->get();

		$count = 0;

		foreach($approvaltable as $row){
			$passed = true;

			if($row->is_check_nominal){
				if(!self::compare($data->grandtotal,$row->sign,$row->nominal)){
					$passed = false;
				}
			}

			if($passed == true){
				$status = $count == 0 ? '1': '0';

				foreach($row->approvalTableDetail as $rowdetail){
					ApprovalMatrix::create([
						'code'						=> strtoupper(Str::random(30)),
						'approval_table_id'			=> $row->id,
						'approval_source_id'		=> $source->id,
						'user_id'					=> $rowdetail->user_id,
						'date_request'				=> date('Y-m-d H:i:s'),
						'status'					=> $status
					]);
				}

				$count++;
			}
		}

		if($count == 0){
			$data = $source->lookable;
			
			$data->update([
				'status'	=> '2'
			]);

			if($table_name == 'good_receipt_mains'){
				self::sendJournal('good_receipt_mains',$data->id,null);
			}else{
				self::sendJournal($table_name,$table_id,$data->account_id);
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

		if($table_name == 'good_receipt_mains'){

			$rgr = GoodReceiptMain::find($table_id);

			foreach($rgr->goodReceipt as $rowgr){

				$journalMap = MenuCoa::whereHas('menu', function($query){
					$query->where('table_name','good_receipt_mains');
				})->where('currency_id',$rowgr->currency_id)->get();

				if(count($journalMap) > 0){
					$arrdata = json_decode(json_encode($rowgr), true);

					$query = Journal::create([
						'user_id'		=> session('bo_id'),
						'account_id'	=> $rowgr->account_id,
						'code'			=> Journal::generateCode(),
						'place_id'		=> $rowgr->place_id,
						'lookable_type'	=> 'good_receipts',
						'lookable_id'	=> $rowgr->id,
						'currency_id'	=> $rowgr->currency_id,
						'currency_rate'	=> $rowgr->currency_rate,
						'post_date'		=> $data->post_date,
						'note'			=> $data->code,
						'status'		=> '3'
					]);

					$arrCoa = [];

					$rgrd = GoodReceiptDetail::where('good_receipt_id',$rowgr->id)->get();

					foreach($rgrd as $rowdetail){
						$index = -1;

						$rowtotal = $rowdetail->getRowTotal() * $rowdetail->goodReceipt->purchaseOrder->currency_rate;

						foreach($arrCoa as $key => $rowcek){
							if($rowcek['coa_id'] == $rowdetail->item->itemGroup->coa_id){
								$index = $key;
							}
						}

						if($index >= 0){
							$arrCoa[$index]['total'] += $rowtotal;
						}else{
							$arrCoa[] = [
								'coa_id'	=> $rowdetail->item->itemGroup->coa_id,
								'total'		=> $rowtotal
							];
						}

						self::sendCogs('good_receipts',
							$rowgr->id,
							$rowgr->company_id,
							$rowgr->place_id,
							$rgr->warehouse_id,
							$rowdetail->item_id,
							$rowdetail->qtyConvert(),
							$rowtotal,
							'IN',
							$rowgr->post_date
						);

						self::sendStock(
							$rowgr->place_id,
							$rgr->warehouse_id,
							$rowdetail->item_id,
							$rowdetail->qtyConvert(),
							'IN'
						);
					}
					
					foreach($arrCoa as $row){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row['coa_id'],
							'place_id'		=> isset($rowgr->place_id) ? $rowgr->place_id : NULL,
							'department_id'	=> isset($rowgr->department_id) ? $rowgr->department_id : NULL,
							'warehouse_id'	=> isset($rgr->warehouse_id) ? $rgr->warehouse_id : NULL,
							'type'			=> '1',
							'nominal'		=> $row['total']
						]);
					}

					foreach($journalMap as $row){
						$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> isset($rowgr->place_id) ? $rowgr->place_id : NULL,
							'department_id'	=> isset($rowgr->department_id) ? $rowgr->department_id : NULL,
							'warehouse_id'	=> isset($rgr->warehouse_id) ? $rgr->warehouse_id : NULL,
							'type'			=> $row->type,
							'nominal'		=> $nominal
						]);
					}
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
				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->depreciation_coa_id,
					'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
					'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
					'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
					'type'			=> '1',
					'nominal'		=> $row->retirement_nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->asset->assetGroup->coa_id,
					'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
					'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
					'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
					'type'			=> '2',
					'nominal'		=> $row->retirement_nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> $row->coa_id,
					'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
					'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
					'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
					'type'			=> '1',
					'nominal'		=> $row->retirement_nominal,
				]);

				JournalDetail::create([
					'journal_id'	=> $query->id,
					'coa_id'		=> Coa::where('code','100.01.01.99.03')->first()->id,
					'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
					'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
					'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
					'type'			=> '2',
					'nominal'		=> $row->retirement_nominal,
				]);
			}
		}elseif($table_name == 'outgoing_payments'){
			$op = OutgoingPayment::find($table_id);
			
			$query = Journal::create([
				'user_id'		=> session('bo_id'),
				'code'			=> Journal::generateCode(),
				'place_id'		=> $op->place_id,
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
					'place_id'		=> isset($row->lookable->place_id) ? $row->lookable->place_id : NULL,
					'department_id'	=> isset($row->lookable->department_id) ? $row->lookable->department_id : NULL,
					'warehouse_id'	=> isset($row->lookable->warehouse_id) ? $row->lookable->warehouse_id : NULL,
					'type'			=> '1',
					'nominal'		=> $row->nominal,
				]);
			}

			$journalMap = MenuCoa::whereHas('menu', function($query) use ($table_name){
				$query->where('table_name',$table_name);
			})
			->whereHas('coa', function($query) use($data){
				$query->where('company_id',Place::find($data->place_id)->company_id);
			})
			->where('currency_id',$data->currency_id)->get();

			if(count($journalMap) > 0){
				$arrdata = get_object_vars($data);

				foreach($journalMap as $row){
					$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

					if($nominal > 0){
						JournalDetail::create([
							'journal_id'	=> $query->id,
							'coa_id'		=> $row->coa_id,
							'place_id'		=> $op->place_id,
							'type'			=> $row->type,
							'nominal'		=> $nominal
						]);
					}
				}
			}

			JournalDetail::create([
				'journal_id'	=> $query->id,
				'coa_id'		=> $op->coa_source_id,
				'place_id'		=> $op->place_id,
				'type'			=> '2',
				'nominal'		=> $op->grandtotal,
			]);
		}else{

			if(isset($data->currency_id)){
				$journalMap = MenuCoa::whereHas('menu', function($query) use ($table_name){
					$query->where('table_name',$table_name);
				})
				->whereHas('coa', function($query) use($data){
					$query->where('company_id',Place::find($data->place_id)->company_id);
				})
				->where('currency_id',$data->currency_id)->get();

				if(count($journalMap) > 0){
					
					$arrdata = get_object_vars($data);

					$query = Journal::create([
						'user_id'		=> session('bo_id'),
						'account_id'	=> $account_id,
						'code'			=> Journal::generateCode(),
						'place_id'		=> $data->place_id,
						'lookable_type'	=> $table_name,
						'lookable_id'	=> $table_id,
						'currency_id'	=> $data->currency_id,
						'currency_rate'	=> $data->currency_rate,
						'post_date'		=> $data->post_date,
						'note'			=> $data->code,
						'status'		=> '3'
					]);
					
					if($table_name == 'landed_costs'){
						$arrCoa = [];

						$lc = LandedCost::find($data->id);
						
						if($lc){
							foreach($lc->landedCostDetail as $rowdetail){
								$pricelc = $rowdetail->nominal / $rowdetail->qty;

								foreach($lc->goodReceiptMain->goodReceipt as $gr){
									$pricenew = 0;
									$itemdata = NULL;
									$itemdata = ItemCogs::where('lookable_type','good_receipts')->where('lookable_id',$gr->id)->where('place_id',$lc->place_id)->where('item_id',$rowdetail->item_id)->first();
									if($itemdata){
										$pricenew = $pricelc + $itemdata->price_in;
										$itemdata->update([
											'price_in'	=> $pricenew,
											'total_in'	=> round($pricenew * $itemdata->qty_in,3),
										]);
									}
								}

								self::resetCogsItem($data->place_id,$rowdetail->item_id);

								$index = -1;

								foreach($arrCoa as $key => $rowcek){
									if($rowcek['coa_id'] == $rowdetail->item->itemGroup->coa_id){
										$index = $key;
									}
								}

								if($index >= 0){
									$arrCoa[$index]['total'] += $rowdetail->nominal;
								}else{
									$arrCoa[] = [
										'coa_id'	=> $rowdetail->item->itemGroup->coa_id,
										'total'		=> $rowdetail->nominal
									];
								}
							}
							
							foreach($arrCoa as $row){
								JournalDetail::create([
									'journal_id'	=> $query->id,
									'coa_id'		=> $row['coa_id'],
									'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
									'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
									'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
									'type'			=> '1',
									'nominal'		=> $row['total']
								]);
							}
						}
					}

					if($table_name == 'capitalizations'){
						$arrCoa = [];

						$cp = Capitalization::find($data->id);
						
						if($cp){
							foreach($cp->capitalizationDetail as $row){
								$index = -1;

								foreach($arrCoa as $key => $rowcek){
									if($rowcek['coa_id'] == $row->asset->assetGroup->coa_id){
										$index = $key;
									}
								}

								if($index >= 0){
									$arrCoa[$index]['total'] += $row->total;
								}else{
									$arrCoa[] = [
										'coa_id'	=> $row->asset->assetGroup->coa_id,
										'total'		=> $row->total
									];
								}
							}
							
							foreach($arrCoa as $row){
								JournalDetail::create([
									'journal_id'	=> $query->id,
									'coa_id'		=> $row['coa_id'],
									'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
									'department_id'	=> isset($data->department_id) ? $data->department_id : NULL,
									'warehouse_id'	=> isset($data->warehouse_id) ? $data->warehouse_id : NULL,
									'type'			=> '1',
									'nominal'		=> $row['total']
								]);
							}
						}
					}

					foreach($journalMap as $row){
						$nominal = $arrdata[$row->field_name] * ($row->percentage / 100);

						if($nominal > 0){
							JournalDetail::create([
								'journal_id'	=> $query->id,
								'coa_id'		=> $row->coa_id,
								'place_id'		=> isset($data->place_id) ? $data->place_id : NULL,
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
			$datatable = DB::table($table_name)->where('id',$table_id)->first();

			foreach($data as $row){
				$item_id = $row->item_id;
				$qty = $row->qty_in ? $row->qty_in : $row->qty_out;
				$type = $row->qty_in ? 'IN' : 'OUT';

				$row->delete();

				self::resetCogsItem($datatable->place_id,$item_id);

				self::resetStock($datatable->place_id,$datatable->warehouse_id,$item_id,$qty,$type);
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
}