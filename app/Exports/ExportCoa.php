<?php

namespace App\Exports;

use App\Models\Coa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportCoa implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $company, $type;

    public function __construct(string $search, string $status, int $company, string $type)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->company = $company ? $company : 0;
        $this->type = $type ? $type : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'PERUSAHAAN',
        'PARENT',
        'LEVEL',
        'AKUN KAS',
        'BLOCK',
        'MUNCUL DI JURNAL',
        'WAJIB BP JURNAL',
        'STATUS',
    ];

    public function collection()
    {
        $data = Coa::where(function ($query) {
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhereHas('company',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->company){
                $query->where('company_id', $this->company);
            }

            if($this->type){
                $query->where(function($query){
                    foreach(explode(',',$this->type) as $row){
                        if($row == '2'){
                            $query->OrWhereNotNull('show_journal');
                        }
                        if($row == '3'){
                            $query->OrWhereNotNull('is_cash_account');
                        }
                    }
                });
            }
        })->orderBy('code')->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'perusahaan'    => $row->company->name,
                'parent'        => $row->parentSub()->exists() ? $row->parentSub->name : 'is Parent',
                'level'         => $row->level,
                'kas'           => $row->is_cash_account ? 'Ya' : 'Tidak',
                'block'         => $row->is_hidden ? 'Ya' : 'Tidak',
                'show_journal'  => $row->show_journal ? 'Ya' : 'Tidak',
                'must_bp'       => $row->bp_journal ? 'Ya' : 'Tidak',
                'status'        => $row->status == '1' ? 'Aktif' : 'Non-Aktif',
            ];
        }

        activity()
                ->performedOn(new Coa())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Coa data.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Coa';
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
