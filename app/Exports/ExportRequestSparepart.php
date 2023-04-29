<?php

namespace App\Exports;

use App\Models\RequestSparepart;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class ExportRequestSparepart implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function view(): View
    {
        return view('admin.exports.request_sparepart', [
            'data' => RequestSparepart::where(function($query) {
                if($this->search) {
                    $query->where(function($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('summary_issue', 'like', "%$this->search%")
                            ->orWhereHas('user', function($query){
                                $query->where('employee_no', 'like', "%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                                
                            });
                    });
                }

                if($this->status){
                    $query->where('status', $this->status);
                }
            })
            ->get()
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
    }

}
