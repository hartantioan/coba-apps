<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\ItemStock;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\View\View;
class ExportSubsidiaryLedger implements  FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $dateend, $datestart, $coaend, $coastart, $closing_journal;

    public function __construct(string $datestart, string $dateend,string $coastart,string $coaend,string $closing_journal)
    {
        $this->datestart = $datestart ? $datestart : '';
		$this->dateend = $dateend ? $dateend : '';
        $this->coastart = $coastart ? $coastart : '';
        $this->coaend = $coaend ? $coaend : '';
        $this->closing_journal = $closing_journal ? $closing_journal : '';
    }
    public function view(): View
    {
        $html=[];
        $coa_start=$this->coastart;
        $coa_end=$this->coaend;
        $coas = Coa::where('status','1')->where('level','5')->whereRaw("code BETWEEN '$coa_start' AND '$coa_end'")->orderBy('code')->get();
        $date_start = $this->datestart;
        $date_end = $this->dateend;
        $array_filter = [];
        foreach($coas as $key => $row){
            $rowdata = null;
            $rowdata = $row->journalDetail()->whereHas('journal',function($query)use($date_start,$date_end){
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'")
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->where('nominal','!=',0)->get();
            $arrData = [];
            foreach($rowdata as $rowdetail){
                $arrData[] = [
                    'post_date' => $rowdetail->journal->post_date,
                    'data'      => $rowdetail,
                ];
            }
            $collect = collect($arrData)->sortBy('post_date');
            $balance = $row->getBalanceFromDate($date_start);
            $data_tempura = [
                'code' => $row->code,
                'name'=>  $row->name,
                'balance'=>number_format($balance,2,',','.'),
            ];
            if(count($collect) > 0){
                foreach($collect as $key => $detail){
                    $additional_ref = '';
                    if($detail['data']->journal->lookable_type == 'outgoing_payments'){
                        $additional_ref = ($detail['data']->note ? ' - ' : '').$detail['data']->journal->lookable->paymentRequest->code;
                    }

                    $balance += ($detail['data']->type == '1' ? round($detail['data']->nominal,2) : round(-1 * $detail['data']->nominal,2));
                    $currencySymbol = $detail['data']->journal->currency()->exists() ? $detail['data']->journal->currency->symbol : '';
                    $nominalCurrency = $detail['data']->journal->currency()->exists() ? ($detail['data']->journal->currency->type == '1' ? '' : '1') : '';

                    $data_tempura['coa_code'][]=$detail['data']->coa->code;
                    $data_tempura['coa_name'][]=$detail['data']->coa->name;
                    $data_tempura['j_postdate'][]=date('d/m/Y',strtotime($detail['data']->journal->post_date));
                    $data_tempura['j_code'][]=$detail['data']->journal->code;
                    $data_tempura['j_lookable'][]=($detail['data']->journal->lookable_id ? $detail['data']->journal->lookable->code : '-');
                    $data_tempura['j_detail1'][]=($detail['data']->type == '1' ? number_format($detail['data']->nominal,2,',','.') : '-');
                    $data_tempura['j_detail2'][]=($detail['data']->type == '2' ? number_format($detail['data']->nominal,2,',','.') : '-');
                    $data_tempura['j_detail3'][]=($detail['data']->type == '1' ? ($nominalCurrency ? $currencySymbol.number_format($detail['data']->nominal_fc,2,',','.') : '-') : '-');
                    $data_tempura['j_detail4'][]=($detail['data']->type == '2' ? ($nominalCurrency ? $currencySymbol.number_format($detail['data']->nominal_fc,2,',','.') : '-') : '-');
                    $data_tempura['j_balance'][]=number_format($balance,2,',','.');
                    $data_tempura['j_note'][]=$detail['data']->journal->note;
                    $data_tempura['j_note1'][]=$detail['data']->note.$additional_ref;
                    $data_tempura['j_note2'][]=$detail['data']->note2;
                    $data_tempura['j_place'][]=($detail['data']->place()->exists() ? $detail['data']->place->code : '-');
                    $data_tempura['j_warehouse'][]=($detail['data']->warehouse()->exists() ? $detail['data']->warehouse->name : '-');
                    $data_tempura['j_line'][]=($detail['data']->line()->exists() ? $detail['data']->line->code : '-');
                    $data_tempura['j_machine'][]=($detail['data']->machine()->exists() ? $detail['data']->machine->code : '-');
                    $data_tempura['j_department'][]=($detail['data']->department()->exists() ? $detail['data']->department->name : '-');
                    $data_tempura['j_project'][]=($detail['data']->project()->exists() ? $detail['data']->project->code : '-');
                }
            }
            $array_filter[]=$data_tempura;
        }
        
        return view('admin.exports.subsidiary_ledger', [
            'data' => $array_filter,
        ]);
    }
}
