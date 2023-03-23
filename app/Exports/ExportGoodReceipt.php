<?php

namespace App\Exports;

use App\Models\GoodReceipt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportGoodReceipt implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $warehouse = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'NO',
        'KODE',
        'PENGGUNA',
        'PO.NO',
        'SUPPLIER',
        'PENERIMA',
        'TGL.POST',
        'TGL.TENGGAT',
        'CABANG',
        'GUDANG',
        'CATATAN',
        'STATUS',
    ];

    public function collection()
    {
        $data = GoodReceipt::where(function ($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('document_date', 'like', "%$this->search%")
                        ->orWhere('receiver_name', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('goodReceiptDetail',function($query){
                            $query->whereHas('item',function($query){
                                $query->where('code', 'like', "%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                            });
                        })
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('purchaseOrder',function($query){
                            $query->where('code','like',"%$this->search%")
                                ->orWhere('note','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->warehouse){
                $arrWarehouse = explode(',',$this->warehouse);
                $query->whereIn('warehouse_id', $arrWarehouse);
            }
        })
        ->whereIn('place_id',$this->dataplaces)
        ->get();

        $arr = [];

        foreach($data as $key => $row){

            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'user'          => $row->user->name,
                'po'            => $row->purchaseOrder->code,
                'supplier'      => $row->supplier->name,
                'penerima'      => $row->receiver_name,
                'tgl_post'      => $row->post_date,
                'tgl_due'       => $row->due_date,
                'cabang'        => $row->branch->name,
                'gudang'        => $row->warehouse->code.' - '.$row->warehouse->name,
                'note'          => $row->note,
                'status'        => $row->statusRaw(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Good Receipt PO';
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
