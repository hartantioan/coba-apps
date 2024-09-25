<?php

namespace App\Exports;

use App\Models\Coa;
use Illuminate\Support\Facades\DB;
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
            $balance_debit = 0;
            $balance_credit = 0;

            $datadebitbefore  = DB::select("
                SELECT 
                    IFNULL(SUM(ROUND(nominal,2)),0) AS total
                FROM journal_details jd
                JOIN journals j
                    ON jd.journal_id = j.id
                WHERE 
                    jd.coa_id = :coa_id 
                    AND jd.deleted_at IS NULL
                    AND j.deleted_at IS NULL
                    AND j.post_date < :date
                    AND jd.type = '1'
                    AND j.status IN ('2','3')
            ", array(
                'coa_id'    => $row->id,
                'date'      => $this->start_date,
            ));

            $datacreditbefore  = DB::select("
                SELECT 
                    IFNULL(SUM(ROUND(nominal,2)),0) AS total
                FROM journal_details jd
                JOIN journals j
                    ON jd.journal_id = j.id
                WHERE 
                    jd.coa_id = :coa_id 
                    AND jd.deleted_at IS NULL
                    AND j.deleted_at IS NULL
                    AND j.post_date < :date
                    AND jd.type = '2'
                    AND j.status IN ('2','3')
            ", array(
                'coa_id'    => $row->id,
                'date'      => $this->start_date,
            ));

            $balance_debit = $datadebitbefore[0]->total;

            $balance_credit = $datacreditbefore[0]->total;

            $balance = $balance_debit - $balance_credit;

            $total_debit = 0;
            $total_credit = 0;

            $ending_debit  = $row->journalDebit()->whereHas('journal',function($query)use($periode){
                $query->whereRaw($periode)
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->get();
            
            $ending_credit = $row->journalCredit()->whereHas('journal',function($query)use($periode){
                $query->whereRaw($periode)
                    ->where(function($query){
                        if($this->closing_journal){
                            $query->where('lookable_type','!=','closing_journals')
                                ->orWhereNull('lookable_type');
                        }
                    });
            })->get();

            foreach($ending_debit as $rowdebit){
                $total_debit += round($rowdebit->nominal,2);
            }

            foreach($ending_credit as $rowcredit){
                $total_credit += round($rowcredit->nominal,2);
            }

            $ending_total  = $balance + $total_debit - $total_credit;

            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->name,
                'company'       => $row->company->name,
                'balance'       => number_format($balance, 2, ',', '.'),
                'ending_debit'  => number_format($total_debit, 2, ',', '.'),
                'ending_credit' => number_format($total_credit, 2, ',', '.'),
                'ending_total'  => number_format($ending_total, 2, ',', '.'),
            ];
        }

        activity()
            ->performedOn(new Coa())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Ledger data.');

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
