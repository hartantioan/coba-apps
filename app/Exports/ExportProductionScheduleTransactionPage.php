<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\ProductionSchedule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionScheduleTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Doner',
        'Tgl.Done',
        'Ket.Done',
        'User',
        'NIK',
        'Perusahaan',
        'Plant',
        'Line',
        'Tanggal Post',
        'Shift Produksi',
        'Keterangan',
        'Lampiran',
        'Status',
    ];

    public function collection()
    {
        $data = ProductionSchedule::where(function($query) {
            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('productionScheduleDetail',function($query) {
                            $query->whereHas('item',function($query) {
                                $query->where('code','like',"%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                            });
                        })
                        ->orWhereHas('place',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('code','like',"%$this->search%");
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
                '2'  => $val->voidUser()->exists() ? $val->voidUser->name : '',
                '3'  => $val->voidUser()->exists() ? $val->void_date : '',
                '4'  => $val->voidUser()->exists() ? $val->void_note : '',
                '5'  => $val->deleteUser()->exists() ? $val->deleteUser->name : '',
                '6'  => $val->deleteUser()->exists() ? $val->deleted_at : '',
                '7'  => $val->deleteUser()->exists() ? $val->delete_note : '',
                '8'  => ($val->status == 3 && is_null($val->done_id)) ? 'sistem' : (($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name : null),
                '9'  => $val->doneUser()->exists() ? $val->done_date : '',
                '10' => $val->doneUser()->exists() ? $val->done_note : '',
                '11' => $val->user->employee_no,
                '12' => $val->user->code,
                '13' => $val->company->name,
                '14' => $val->place->code,
                '15' => $val->line->code,
                '16' => date('d/m/Y', strtotime($val->post_date)),
                '17' => $val->note,
                '18' => $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                '19' => $val->statusRaw(),
            ];
        
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Jadwal Produksi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
