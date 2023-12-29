<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportPurchaseRequest implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $dataplaces, $mode;

    public function __construct(string $start_date, string $end_date, array $dataplaces, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
        $this->mode = $mode ? $mode : '';
    }

    private $headings = [
        'No',
        'Document Number',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'WareHouse Code',
        'Posting Date',
        'Item Code',
        'Item Description',
        'Quantity',
        'Unit',
        'Note 1',
        'Note 2',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseRequestDetail::whereHas('purchaseRequest', function($query) {
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }elseif($this->mode == '2'){
            $data = PurchaseRequestDetail::withTrashed()->whereHas('purchaseRequest', function($query) {
                $query->withTrashed()->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->purchaseRequest->code,
                'status'            => $row->purchaseRequest->statusRaw(),
                'voider'            => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->voidUser->name : '',
                'void_date'         => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->void_date : '',
                'void_note'         => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->void_note : '',
                'deleter'           => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->deleteUser->name : '',
                'delete_date'       => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->deleted_at : '',
                'delete_note'       => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->delete_note : '',
                'warehouse_code'    => $row->place->code,
                'post_date'         => date('d/m/y',strtotime($row->purchaseRequest->post_date)),
                'item_code'         => $row->item->code,
                'item'              => $row->item->name,
                'qty'               => $row->qty,
                'unit'              => $row->item->buyUnit->code,
                'remarks'           => $row->note,
                'free_text'         => $row->note2,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap Purchase Request';
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
