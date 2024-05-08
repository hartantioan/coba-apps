<?php

namespace App\Exports;

use App\Models\ProductionIssueReceive;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionIssueReceiveTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $status, $end_date, $start_date , $search;
    public function __construct(string $search,string $status, string $end_date, string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
    }

    private $headings = [
        'No',
        'Kode Jadwal',
        'User',
        'Perusahaan',
        'Tanggal Post',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Doner',
        'Tgl.Done',
        'Ket.Done',
        'Note',
        'Kode Produksi',
        'Kode Jadwal Produksi',
        'Shift Produksi',
        'Proses Mulai',
        'Proses Akhir',
        'Line',
        'Group',
        'Plant',
        'Mesin',
        'Lampiran',
        'Status',
    ];

    public function collection()
    {
        $data = ProductionIssueReceive::where(function($query) {
            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('note','like',"%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->whereIn('status', $this->status);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

        })
        ->get();

    

        foreach($data as $key => $val){
            
            $arr[] = [
                '0'  => $key,
                '1'  => $val->code,
                '2'  => $val->user->name,
                '3'  => $val->company->name,
                '4'  => date('d/m/Y',strtotime($val->post_date)),
                '5'  => $val->voidUser()->exists() ? $val->voidUser->name : '',
                '6'  => $val->voidUser()->exists() ? $val->void_date : '',
                '8'  => $val->voidUser()->exists() ? $val->void_note : '',
                '9'  => $val->deleteUser()->exists() ? $val->deleteUser->name : '',
                '10'  => $val->deleteUser()->exists() ? $val->deleted_at : '',
                '11'  => $val->deleteUser()->exists() ? $val->delete_note : '',
                '12'  => ($val->status == 3 && is_null($val->done_id)) ? 'sistem' : (($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name : null),
                '13'  => $val->doneUser()->exists() ? $val->done_date : '',
                '14' => $val->doneUser()->exists() ? $val->done_note : '',
                '15' => $val->user->name,
                '16' => $val->company->name,
                '17' => $val->place->code,
                '18' => $val->line->code,
                '19' => date('d/m/Y', strtotime($val->post_date)),
                '20' => $val->note,
                '21' => $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                '22' => $val->statusRaw(),
            ];
        
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Issue Produksi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
