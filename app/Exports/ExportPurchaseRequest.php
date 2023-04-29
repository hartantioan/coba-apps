<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportPurchaseRequest implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status, array $dataplaces)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'ID',
        'PENGGUNA',
        'KODE',
        'PENGAJUAN',
        'KADALUARSA',
        'DIPAKAI',
        'KETERANGAN',
        'STATUS',
        'ITEM',
        'QTY',
        'SATUAN',
        'CATATAN',
        'TGL.PAKAI'
    ];

    public function collection()
    {
        $data = PurchaseRequestDetail::whereHas('purchaseRequest', function($query){
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('required_date', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
        })->where(function($query){
            if($this->search){
                $query->whereHas('item',function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name','like',"%$this->search%");
                });
            }
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'name'          => $row->purchaseRequest->user->name,
                'code'          => $row->purchaseRequest->code,
                'post_date'     => $row->purchaseRequest->post_date,
                'due_date'      => $row->purchaseRequest->due_date,
                'required_date' => $row->purchaseRequest->required_date,
                'note'          => $row->purchaseRequest->note,
                'status'        => $row->purchaseRequest->statusRaw(),
                'item'          => $row->item->name,
                'qty'           => $row->qty,
                'unit'          => $row->item->buyUnit->code,
                'note_item'     => $row->note,
                'used_date'     => $row->required_date  
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Request';
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
