<?php

namespace App\Exports;

use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\ProductionHandover;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionRepackDetail;
use App\Models\UserBrand;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportSalesBrand implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }
    private $headings = [
        'Kode',
        'Tanggal',
        'Customer',
        'Alamat Kirim',
        'Item Code',
        'Item Name',
        'Shading',
        'Qty SJ',
        'Satuan',
        'Qty M2',
        'Satuan',
        'Tipe',
        'Sopir',
        'Truk',
        'Nopol',
        'PO Cust.'


    ];



    public function collection()
    {

        $userbrand = UserBrand::where('account_id', session('bo_id'))->get('brand_id')->toArray();
        $brand = [];
        foreach ($userbrand as $key => $row) {
           array_push($brand,$row['brand_id'] );
        }

        $mo = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->finish_date)->where('deleted_at',NULL)->whereIN('status',[2,3]);
        })->get();


        $arr = [];

        foreach ($mo as $key => $row) {


            if (in_array($row->marketingOrderDeliveryDetail->item->brand_id, $brand)) {


                $arr[] = [
                    'code'              => $row->marketingOrderDeliveryProcess->code,
                    'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),
                    'customer' => $row->marketingOrderDeliveryDetail->marketingOrderDelivery->customer->name,
                    'alamat_tujuan' => $row->marketingOrderDeliveryDetail->marketingOrderDelivery->destination_address,
                    'itemcode' => $row->marketingOrderDeliveryDetail->item->code,
                    'itemname' => $row->marketingOrderDeliveryDetail->item->name,
                    'shading' => $row->itemStock->itemShading->code,
                    'qtysj' => $row->marketingOrderDeliveryDetail->qty,

                    'satuan_konversi' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit->unit->code,
                    'qty' => $row->qty * $row->marketingOrderDeliveryDetail->getQtyM2(),
                    'satuan' => $row->itemStock->item->uomUnit->code,

                    'delivery_type' => $row->marketingOrderDeliveryDetail->marketingOrderDelivery->deliveryType(),
                    'sopir'                => $row->marketingOrderDeliveryProcess->driver_name,
                    'truk' => $row->marketingOrderDeliveryProcess->vehicle_name,
                    'nopol' => $row->marketingOrderDeliveryProcess->vehicle_no,

                    'po_customer' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->document_no,
                ];
            }
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Sales';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
