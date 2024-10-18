<?php

namespace App\Exports;

use App\Models\Coa;
use App\Models\ItemStock;
use App\Models\JournalDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\DB;
class ExportSubsidiaryLedger implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
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

    private $headings = [
        'KODE COA',
        'NAMA COA',
        'TGL.POST',
        'NO.JE',
        'DOK.REF.',
        'DEBIT FC',
        'KREDIT FC',
        'DEBIT RP',
        'KREDIT RP',
        'TOTAL RP',
        'KETERANGAN 1',
        'KETERANGAN 2',
        'KETERANGAN 3',
        'PLANT',
        'GUDANG',
        'LINE',
        'MESIN',
        'DIVISI',
        'PROYEK',
    ];

    public function collection()
    {
        $coa_start=$this->coastart;
        $coa_end=$this->coaend;
        $coas = Coa::where('status','1')->where('level','5')->whereRaw("code BETWEEN '$coa_start' AND '$coa_end'")->orderBy('code')->get();
        $date_start = $this->datestart;
        $date_end = $this->dateend;
        $arr = [];
        foreach($coas as $key => $row){
            $rowdata = $row->journalDetail()
            ->selectRaw('*,journal_details.note AS notekuy')
            ->whereHas('journal',function($query)use($date_start,$date_end){
                $query->whereRaw("journals.post_date BETWEEN '$date_start' AND '$date_end'")
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('journals.lookable_type','!=','closing_journals')
                                ->orWhereNull('journals.lookable_type');
                        }
                    });
            })
            ->where('journal_details.nominal','!=',0)
            ->join('journals', 'journal_details.journal_id', '=', 'journals.id')
            ->orderBy('journals.post_date', 'ASC')->get();
            $balance = $row->getBalanceFromDate($date_start);
            $arr[] = [
                'code'      => $row->code,
                'name'      => $row->name,
                'post_date' => '',
                'je_no'     => '',
                'ref_doc'   => '',
                'debit_fc'  => '',
                'credit_fc' => '',
                'debit_rp'  => '',
                'credit_rp' => '',
                'balance'   => ($balance != 0 ? number_format($balance, 2, ',', '.') : 0),
                'note1'     => '',
                'note2'     => '',
                'note3'     => '',
                'place'     => '',
                'warehouse' => '',
                'line'      => '',
                'machine'   => '',
                'division'  => '',
                'project'   => '',
            ];
            foreach($rowdata as $rowdetail){
                $additional_ref = '';
                if($rowdetail->journal->lookable_type == 'outgoing_payments'){
                    $additional_ref = ($rowdetail->note ? ' - ' : '').$rowdetail->journal->lookable->paymentRequest->code;
                }
                $balance = $rowdetail->type == '1' ? $balance + round($rowdetail->nominal,2) : $balance - round($rowdetail->nominal,2);

                if($rowdetail->detailable_id != null && $rowdetail->detailable_type == 'marketing_order_delivery_process_details'){
                    $info = $rowdetail->detailable->marketingOrderDeliveryProcess->code;
                }else{
                    $info = $rowdetail->notekuy.$additional_ref;
                }
                $arr[] = [
                    'code'      => $row->code,
                    'name'      => $row->name,
                    'post_date' => $rowdetail->journal->post_date,
                    'je_no'     => $rowdetail->journal->code,
                    'ref_doc'   => $rowdetail->journal->lookable_id ? $rowdetail->journal->lookable->code : '-',
                    'debit_fc'  => $rowdetail->type == '1' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0',
                    'credit_fc' => $rowdetail->type == '2' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0',
                    'debit_rp'  => $rowdetail->type == '1' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0',
                    'credit_rp' => $rowdetail->type == '2' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0',
                    'balance'   => number_format($balance, 2, ',', '.'),
                    'note1'     => $rowdetail->journal->note,
                    'note2'     => $info,
                    'note3'     => $rowdetail->note2,
                    'place'     => $rowdetail->place()->exists() ? $rowdetail->place->code : '-',
                    'warehouse' => $rowdetail->warehouse()->exists() ? $rowdetail->warehouse->name : '-',
                    'line'      => $rowdetail->line()->exists() ? $rowdetail->line->code : '-',
                    'machine'   => $rowdetail->machine()->exists() ? $rowdetail->machine->code : '-',
                    'division'  => $rowdetail->department()->exists() ? $rowdetail->department->name : '-',
                    'project'   => $rowdetail->project()->exists() ? $rowdetail->project->code : '-',
                ];
            }
        }
        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Subsidiary Ledger';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}
