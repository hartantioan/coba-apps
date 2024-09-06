<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use App\Models\Coa;
use App\Models\ItemCogs;
use App\Models\Journal;
use App\Models\JournalDetail;
use App\Models\LandedCost;
use App\Models\ProductionIssue;
use Illuminate\Console\Command;

class SendJournal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:journal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send journaling';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /* $lc = LandedCost::find(345);
			
        if($lc){
            $arrNote = [];

            $otherLc = NULL;

            $coaselisihhargabahan = Coa::where('code','500.02.01.13.01')->where('company_id',$lc->company_id)->where('status','1')->first();

            $query = Journal::create([
                'user_id'		=> $lc->user_id,
                'company_id'	=> $lc->company_id,
                'code'			=> Journal::generateCode('JOEN-'.date('y',strtotime($lc->post_date)).'00'),
                'lookable_type'	=> 'landed_costs',
                'lookable_id'	=> $lc->id,
                'post_date'		=> $lc->post_date,
                'note'			=> $lc->note,
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
                        'status_lc' => '2'
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
                            'lookable_type'	=> $lc->getTable(),
                            'lookable_id'	=> $lc->id,
                            'detailable_type'=> $rowdetail->getTable(),
                            'detailable_id'	=> $rowdetail->id,
                        ]);

                        CustomHelper::sendCogs('landed_costs',
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
                            'lookable_type'	=> $lc->getTable(),
                            'lookable_id'	=> $lc->id,
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
                            'lookable_type'	=> $lc->getTable(),
                            'lookable_id'	=> $lc->id,
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
                        'lookable_type'	=> $lc->getTable(),
                        'lookable_id'	=> $lc->id,
                        'detailable_type'=> $rowdetail->getTable(),
                        'detailable_id'	=> $rowdetail->id,
                    ]);
                    $totalfccost += $rowdetail->total;
                }
            }

            $balance = $totalitem - $totalcost;
            $balancefc = $totalfcitem - $totalfccost;
            if($balance < 0 || $balance > 0){
                $coarounding = Coa::where('code','700.01.01.01.05')->where('company_id',$lc->company_id)->first();
                JournalDetail::create([
                    'journal_id'	=> $query->id,
                    'coa_id'		=> $coarounding->id,
                    'account_id'	=> $coarounding->bp_journal ? $lc->account->id : NULL,
                    'type'			=> $balance < 0 ? '1' : '2',
                    'nominal'		=> abs($balance),
                    'nominal_fc'	=> $balancefc,
                    'lookable_type'	=> $lc->getTable(),
                    'lookable_id'	=> $lc->id,
                ]);
            }
        } */

        $data = ProductionIssue::whereIn('code',['ISFP-24P1-00000032','ISFP-24P1-00000031','ISFP-24P1-00000030','ISFP-24P1-00000025','ISFP-24P1-00000024','ISFP-24P1-00000020','ISFP-24P1-00000019','ISFP-24P1-00000018','ISFP-24P1-00000015','ISFP-24P1-00000014','ISFP-24P1-00000013','ISFP-24P1-00000010','ISFP-24P1-00000009','ISFP-24P1-00000008','ISFP-24P1-00000002','ISFP-24P1-00000001'])->get();

        foreach($data as $rowkambing){
            foreach($rowkambing->productionIssueDetail()->where('lookable_type','items')->get() as $rowdetail){
                $itemcogs2 = ItemCogs::where('date','>=',$rowkambing->post_date)->where('company_id',$rowkambing->company_id)->where('place_id',$rowkambing->place_id)->where('item_id',$rowdetail->lookable_id)->orderBy('date')->orderBy('id')->get();
                $old_data2 = ItemCogs::where('date','<',$rowkambing->post_date)->where('company_id',$rowkambing->company_id)->where('place_id',$rowkambing->place_id)->where('item_id',$rowdetail->lookable_id)->orderByDesc('date')->orderByDesc('id')->first();
        
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
            
                            $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
                        }else{
                            if($row->type == 'IN'){
                                $total_final = $row->total_in;
                                $qty_final = $row->qty_in;
                            }elseif($row->type == 'OUT'){
                                $total_final = 0 - $row->total_out;
                                $qty_final = 0 - $row->qty_out;
                            }
                
                            $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
                        }
                        $row->update([
                            'price_final'	=> $price_final,
                            'qty_final'		=> $qty_final,
                            'total_final'	=> $total_final,
                        ]);
                    }else{
                        if($row->type == 'IN'){
                            $total_final += $row->total_in;
                            $qty_final += $row->qty_in;
                        }elseif($row->type == 'OUT'){
                            $total_final -= $row->total_out;
                            $qty_final -= $row->qty_out;
                        }
                        $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
                        $row->update([
                            'price_final'	=> $price_final,
                            'qty_final'		=> $qty_final,
                            'total_final'	=> $total_final,
                        ]);
                    }
                }
            }
        }
    }
}
