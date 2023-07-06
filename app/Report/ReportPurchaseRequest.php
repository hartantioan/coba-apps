<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ReportPurchaseRequest implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $post_date, string $end_date, array $dataplaces)
    {
        $this->post_date = $post_date ? $post_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'Document Number',
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
        $data = PurchaseRequestDetail::whereHas('purchaseRequest', function($query) {
            $query->where('post_date', '>=',$this->post_date)
                  ->where('post_date', '<=', $this->end_date);
        })->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'                => ($key + 1),
                'name'              => $row->purchaseRequest->user->name,
                'code'              => $row->purchaseRequest->code,
                'post_date'         => $row->purchaseRequest->post_date,
                'due_date'          => $row->purchaseRequest->due_date,
                'required_date'     => $row->purchaseRequest->required_date,
                'note'              => $row->purchaseRequest->note1,
                'status'            => $row->purchaseRequest->statusRaw(),
                'item_code'         => $row->item->code,
                'item'              => $row->item->name,
                'qty'               => $row->qty,
                'unit'              => $row->item->buyUnit->code,
                'remarks'           => $row->note2,
                'warehouse_code'    => $row->place_id  
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
