<?php

namespace App\Exports;

use App\Models\Coa;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\DB;
class ExportSubsidiaryLedger2 implements FromArray, WithTitle, WithHeadings, WithCustomStartCell
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

    public function array(): array
    {
        $coa_start=$this->coastart;
        $coa_end=$this->coaend;
        $datas = Coa::where('status','1')->where('level','5')->where('code','>=',$coa_start)->where('code','<=',$coa_end)->orderBy('code')->get();

        $additonal_condition = '';

        if($this->closing_journal){
            $additonal_condition = "AND (j.lookable_type != 'closing_journals' OR j.lookable_type IS NULL)";
        }

        foreach($datas as $key => $row){            
            $query = DB::select("
                SELECT
                    jd.*,
                    j.code as code,
                    j.post_date as post_date,
                    j.note as headernote,
                    p.code as place_code,
                    w.name as warehouse_name,
                    l.code as line_code,
                    m.name as machine_name,
                    d.name as division_name,
                    pj.name as project_name,
                    j.lookable_type as lookable_type,
                    j.lookable_id as lookable_id
                    FROM 
                        journal_details jd
                    LEFT JOIN coas c
                        ON c.id = jd.coa_id
                    LEFT JOIN journals j
                        ON j.id = jd.journal_id
                    LEFT JOIN places p
                        ON p.id = jd.place_id
                    LEFT JOIN warehouses w
                        ON w.id = jd.warehouse_id
                    LEFT JOIN `lines` l
                        ON l.id = jd.line_id
                    LEFT JOIN machines m
                        ON m.id = jd.machine_id
                    LEFT JOIN divisions d
                        ON d.id = jd.department_id
                    LEFT JOIN projects pj
                        ON pj.id = jd.project_id
                    LEFT JOIN j.lookable_type lb
                        ON lb.id = j.lookable_id
                    WHERE
                        jd.deleted_at IS NULL
                        AND j.deleted_at IS NULL
                        AND j.status = '3'
                        AND jd.coa_id = :coa_id
                        AND j.post_date >= :datestart
                        AND j.post_date <= :dateend
                        AND jd.nominal <> 0
                        ".$additonal_condition."
                    ORDER BY j.post_date ASC
            ", [
                'coa_id'        => $row->id,
                'datestart'     => $this->datestart,
                'dateend'       => $this->dateend,
            ]);
            $balance = $row->getBalanceFromDate($this->datestart);
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
            foreach($query as $rowdetail){
                $additional_ref = '';
                
                $balance = $rowdetail->type == '1' ? $balance + round($rowdetail->nominal,2) : $balance - round($rowdetail->nominal,2);

                $arr[] = [
                    'code'      => $row->code,
                    'name'      => $row->name,
                    'post_date' => $rowdetail->post_date,
                    'je_no'     => $rowdetail->code,
                    'ref_doc'   => '-',
                    'debit_fc'  => $rowdetail->type == '1' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0',
                    'credit_fc' => $rowdetail->type == '2' && $rowdetail->nominal_fc != 0 ? number_format($rowdetail->nominal_fc,2,',','.') : '0',
                    'debit_rp'  => $rowdetail->type == '1' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0',
                    'credit_rp' => $rowdetail->type == '2' && $rowdetail->nominal != 0 ? number_format($rowdetail->nominal,2,',','.') : '0',
                    'balance'   => number_format($balance, 2, ',', '.'),
                    'note1'     => $rowdetail->headernote,
                    'note2'     => $rowdetail->note,
                    'note3'     => $rowdetail->note2,
                    'place'     => $rowdetail->place_code,
                    'warehouse' => $rowdetail->warehouse_name,
                    'line'      => $rowdetail->line_code,
                    'machine'   => $rowdetail->machine_name,
                    'division'  => $rowdetail->division_name,
                    'project'   => $rowdetail->project_name,
                ];
            }
        }

        return $arr;
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
