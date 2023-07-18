<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPurchaseMemo implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';

    }

    private $headings = [
        'NO',
        'POSTING DATE',
        'CODE',
        'SUPPLIER CODE',
        'SUPPLIER NAME',
        'TGL.POST',
        'TGL.TENGGAT',
        'TAX CODE',
        'TAX NAME',
        'TIPE',
        'KETERANGAN',
        'STATUS',
    ];

    public function collection()
    {
        $data = PurchaseMemo::where(function ($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date);
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                => ($key + 1),
                '2'              => date('d/m/y',strtotime($row->post_date)),
                '3'                 => $row->code,
                '4'         => $row->supplier->employee_no ?? '',
                '5'          => $row->supplier->name ?? '',
                '6'              => date('d/m/y',strtotime($row->post_date)),
                '8'              => date('d/m/y',strtotime($row->due_date)),
                '11'            => $row->tax->code ?? '',
                '12'            => $row->tax->name ?? '',
                '7'              => $row->type,
                '9'           => $row->note,
                '14'              => $row->statusRaw(),
            ];
        }

        return collect($arr);
    }
    // public function view(): View
    // {
    //     return view('admin.exports.purchase_memo', [
    //         'data' => PurchaseMemo::where(function($query){
    //             if($this->search) {
    //                 $query->where(function($query){
    //                     $query->where('code', 'like', "%$this->search%")
    //                         ->orWhere('post_date', 'like', "%$this->search%")
    //                         ->orWhere('total', 'like', "%$this->search%")
    //                         ->orWhere('tax', 'like', "%$this->search%")
    //                         ->orWhere('wtax', 'like', "%$this->search%")
    //                         ->orWhere('grandtotal', 'like', "%$this->search%")
    //                         ->orWhere('note', 'like', "%$this->search%")
    //                         ->orWhereHas('user',function($query){
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         });
    //                 });
    //             }

    //             if($this->status){
    //                 $query->where('status', $this->status);
    //             }

    //             if($this->start_date && $this->finish_date) {
    //                 $query->whereDate('post_date', '>=', $this->start_date)
    //                     ->whereDate('post_date', '<=', $this->finish_date);
    //             } else if($this->start_date) {
    //                 $query->whereDate('post_date','>=', $this->start_date);
    //             } else if($this->finish_date) {
    //                 $query->whereDate('post_date','<=', $this->finish_date);
    //             }

    //             if($this->account){
    //                 $query->whereIn('account_id',$this->account);
    //             }
                
    //             if($this->company){
    //                 $query->where('company_id',$this->company);
    //             }
    //         })
    //         ->get()
    //     ]);
    // }

    public function title(): string
    {
        return 'Laporan Purchase Down Payment';
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
