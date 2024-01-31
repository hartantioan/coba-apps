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
    protected $dateend, $datestart, $coaend, $coastart;

    public function __construct(string $datestart, string $dateend,string $coastart,string $coaend)
    {
        $this->datestart = $datestart ? $datestart : '';
		$this->dateend = $dateend ? $dateend : '';
        $this->coastart = $coastart ? $coastart : '';
        $this->coaend = $coaend ? $coaend : '';
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
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'");
            })->where('nominal','!=',0)->get();
            $balance = $row->getBalanceFromDate($date_start);
            $data_tempura = [
                'code' => $row->code,
                'name'=>  $row->name,
                'balance'=>number_format($balance,2,',','.'),
            ];
            if(count($rowdata) > 0){
                foreach($rowdata as $key => $detail){
                    $balance += ($detail->type == '1' ? $detail->nominal : -1 * $detail->nominal);

                    $data_tempura['coa_code'][]=$detail->coa->code;
                    $data_tempura['coa_name'][]=$detail->coa->name;
                    $data_tempura['j_postdate'][]=date('d/m/Y',strtotime($detail->journal->post_date));
                    $data_tempura['j_code'][]=$detail->journal->code;
                    $data_tempura['j_lookable'][]=($detail->journal->lookable_id ? $detail->journal->lookable->code : '-');
                    $data_tempura['j_detail1'][]=($detail->type == '1' ? number_format($detail->nominal,2,',','.') : '-');
                    $data_tempura['j_detail2'][]=($detail->type == '2' ? number_format($detail->nominal,2,',','.') : '-');
                   
                    $data_tempura['j_balance'][]=number_format($balance,2,',','.');
                    $data_tempura['j_note'][]=$detail->journal->note;
                    $data_tempura['j_note1'][]=$detail->note;
                    $data_tempura['j_note2'][]=$detail->note2;
                    $data_tempura['j_place'][]=($detail->place()->exists() ? $detail->place->code : '-');
                    $data_tempura['j_warehouse'][]=($detail->warehouse()->exists() ? $detail->warehouse->name : '-');
                    $data_tempura['j_line'][]=($detail->line()->exists() ? $detail->line->code : '-');
                    $data_tempura['j_machine'][]=($detail->machine()->exists() ? $detail->machine->code : '-');
                    $data_tempura['j_department'][]=($detail->department()->exists() ? $detail->department->code : '-');
                    $data_tempura['j_project'][]=($detail->project()->exists() ? $detail->project->code : '-');
                }
            }
            $array_filter[]=$data_tempura;
        }
        
        return view('admin.exports.subsidiary_ledger', [
            'data' => $array_filter,
        ]);
    }
}
