<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\ItemStock;
use App\Models\JournalDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
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
            /* $rowdata = JournalDetail::where('coa_id',$row->id)->whereHas('journal',function($query)use($date_start,$date_end){
                $query->whereRaw("post_date BETWEEN '$date_start' AND '$date_end'")
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    })->orderBy('post_date');
            })->get(); */
            $rowdata = JournalDetail::where('coa_id',$row->id)
                ->whereRaw("journals.post_date BETWEEN '$date_start' AND '$date_end'")
                ->where(function($query){
                    if($this->closing_journal){
                        $query->where('journals.lookable_type','!=','closing_journals')
                            ->orWhereNull('journals.lookable_type');
                    }
                })
                ->whereIn('journals.status',['2','3'])
                ->join('journals', 'journals.id', '=', 'journal_details.journal_id')
                ->orderBy('journals.post_date')
                ->get();
            $balance = $row->getBalanceFromDate($date_start);
            $data_tempura = [
                'code'      => $row->code,
                'name'      => $row->name,
                'balance'   => ($balance != 0 ? number_format($balance, 2, ',', '.') : '-'),
                'details'   => $rowdata,
            ];
            $array_filter[]=$data_tempura;
        }
        
        return view('admin.exports.subsidiary_ledger', [
            'data' => $array_filter,
        ]);
    }
}
