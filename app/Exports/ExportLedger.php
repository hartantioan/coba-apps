<?php

namespace App\Exports;

use App\Models\Coa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLedger implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $coa_id, $company_id, $search, $closing_journal;

    public function __construct(string $start_date, string $end_date, string $coa_id, string $company_id, string $search,string $closing_journal)
    {
        $this->start_date = $start_date ?? '';
		$this->end_date = $end_date ?? '';
        $this->coa_id = $coa_id ?? '';
        $this->company_id = $company_id ?? '';
        $this->search = $search ?? '';
        $this->closing_journal = $closing_journal ?? '';
    }


    private $headings = [
        'No',
        'Kode Coa',
        'Nama Coa',
        'Perusahaan',
        'Saldo Awal',
        'Debit',
        'Kredit',
        'Saldo Akhir',
    ];

    public function collection()
    {
        info($this->coa_id);
        $query_data = Coa::where(function($query) {
            if($this->search !== '') {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->coa_id !== '') {
                $query->where('id', intval($this->coa_id));
            }
        })
        ->where('company_id',intval($this->company_id))
        ->where('level','5')
        ->where('status', 1)
        ->get();

        $arr = [];

        foreach($query_data as $key => $row){
            if($this->start_date && $this->end_date) {
                $periode = "DATE(post_date) >= '$this->start_date' AND DATE(post_date) <= '$this->end_date'";
            } else if($this->start_date) {
                $periode = "DATE(post_date) >= '$this->start_date' AND DATE(post_date) <= CURDATE()";
            } else if($this->end_date) {
                $periode = "DATE(post_date) >= CURDATE() AND DATE(post_date) <= '$this->end_date'";
            } else {
                $periode = "";
            }
            $balance_debit  = $row->journalDebit()->whereHas('journal',function($query){
                $query->whereDate('post_date','<',$this->start_date);
            })->sum('nominal');
            $balance_credit  = $row->journalCredit()->whereHas('journal',function($query){
                $query->whereDate('post_date','<',$this->start_date);
            })->sum('nominal');

            $balance = $balance_debit - $balance_credit;

            $ending_debit  = $row->journalDebit()->whereHas('journal',function($query)use($periode){
                $query->whereRaw($periode)
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->sum('nominal');
            
            $ending_credit = $row->journalCredit()->whereHas('journal',function($query)use($periode){
                $query->whereRaw($periode)
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->sum('nominal');

            $ending_total  = $balance + $ending_debit - $ending_credit;

            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->name,
                'company'       => $row->company->name,
                'balance'       => number_format($balance, 2, ',', '.'),
                'ending_debit'  => number_format($ending_debit, 2, ',', '.'),
                'ending_credit' => number_format($ending_credit, 2, ',', '.'),
                'ending_total'  => number_format($ending_total, 2, ',', '.'),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Buku Besar';
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
