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

    public function __construct(string $start_date, string $end_date, array $dataplaces)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'No',
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
            $query->where('post_date', '>=',$this->start_date)
                  ->where('post_date', '<=', $this->end_date);
        })->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->purchaseRequest->code,
                'warehouse_code'    => $row->place->code,
                'post_date'         => $row->purchaseRequest->post_date,
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
