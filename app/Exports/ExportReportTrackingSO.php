<?php

namespace App\Exports;

use App\Models\MarketingOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportTrackingSO implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Status',
        'Kode',
        'Customer',
        'Void User',
        'Void Date',
        'Void Note',
        'Delete User',
        'Delete Date',
        'Delete Note',
        'Doner',
        'Done Date',
        'Done Note',
        'Post Date',
        'Top Customer',
        'Type',
        'Document No',
        'Delivery Type',
        'Destination Address',
        'Province',
        'City',
        'District',
        'Internal Note',
        'External Note',
        'Item Code',
        'Item Name',
        'Employee No',
        'User',
        'Price',
        'Discount 1',
        'Discount 2',
        'Discount 3',
        'Transportation',
        'Payment Type',
        'Quantity SO(M2)',
        'Sisa SO (M2)',
        'Qty MOD (M2)',
        'Delivery (M2)',
        'Quantity SO(Box)',
        'Sisa SO (Box)',
        'Qty MOD (Box)',
        'Delivery (Box)',

    ];

    public function collection()
    {
        $mo = MarketingOrderDetail::whereHas('marketingOrder', function ($query) {
            $query->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->finish_date);
        })->get();


        $arr = [];
        foreach ($mo as $key => $row) {

            $total_qty_modm2 = $row->totalQtyModM2();

            $total_qty_dom2 = $row->totalQtyDOM2();
            $arr[] = [
                'no'    => ($key+1),
                'status'=> $row->marketingOrder->statusRaw(),
                'code'              => $row->marketingOrder->code,
                'customer'          => $row->marketingOrder->account->name,
                'voider'            => $row->marketingOrder->voidUser()->exists() ? $row->marketingOrder->voidUser->name : '',
                'void_date'         => $row->marketingOrder->voidUser()->exists() ? $row->marketingOrder->void_date : '',
                'void_note'         => $row->marketingOrder->voidUser()->exists() ? $row->marketingOrder->void_note : '',
                'deleter'           => $row->marketingOrder->deleteUser()->exists() ? $row->marketingOrder->deleteUser->name : '',
                'delete_date'       => $row->marketingOrder->deleteUser()->exists() ? $row->marketingOrder->deleted_at : '',
                'delete_note'       => $row->marketingOrder->deleteUser()->exists() ? $row->marketingOrder->delete_note : '',
                'doner'             => ($row->marketingOrder->status == 3 && is_null($row->marketingOrder->done_id)) ? 'sistem' : (($row->marketingOrder->status == 3 && !is_null($row->marketingOrder->done_id)) ? $row->marketingOrder->doneUser->name : null),
                'done_date'         => $row->marketingOrder->doneUser()->exists() ? $row->marketingOrder->done_date : '',
                'done_note'         => $row->marketingOrder->doneUser()->exists() ? $row->marketingOrder->done_note : '',
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrder->post_date)),
                'top'               => $row->marketingOrder->top_customer,
                'tipe'              => $row->marketingOrder->type(),
                'po'                => $row->marketingOrder->document_no,
                'pengiriman'                => $row->marketingOrder->deliveryType(),
                'alamatkirim'                => $row->marketingOrder->destination_address,
                'provinsi' => $row->marketingOrder->province->name,
                'kota' => $row->marketingOrder->city->name,
                'kecamatan' => $row->marketingOrder->district->name,
                'noteinternal' => $row->marketingOrder->note_internal,
                'noteexternal' => $row->marketingOrder->note_external,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,
                'nik' => $row->marketingOrder->user->employee_no,
                'user' => $row->marketingOrder->user->name,
                'price' => $row->price,
                'disc1' => $row->percent_discount_1,
                'disc2' => $row->percent_discount_2,
                'disc3' => $row->discount_3,
                'truck' => $row->marketingOrder->transportation->name,
                'pembayaran' => $row->marketingOrder->paymentType(),
                'qty' => $row->qty_uom,
                'sisa_so_m2' => $row->qty_uom - $total_qty_modm2 ,
                'qty_mod_m2' => $total_qty_modm2,
                'delivery_m2' => $total_qty_dom2,
                'qty_box' => $row->totalQtyBox(),
                'sisa_so_box' => $row->totalQtyBox() - $row->totalQtyModBox() ,
                'qty_mod_box' => $row->totalQtyModBox(),
                'delivery_box' => $row->totalQtyDOBox(),


            ];
        }

        return collect($arr);


    }

    public function title(): string
    {
        return 'Tracking SO Report';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
