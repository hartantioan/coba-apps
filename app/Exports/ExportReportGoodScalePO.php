<?php

namespace App\Exports;

use App\Models\GoodScale;
use App\Models\GoodScaleDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
class ExportReportGoodScalePO implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $start_date, $finish_date, $status,$type,$status_qc;

    public function __construct(string $start_date, string $finish_date, string $status, string $type,$status_qc)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->finish_date = $finish_date ? $finish_date : '';
        $this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->status_qc = $status_qc ? $status_qc : '';

    }

    private $headings = [
        'No',
        'No Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'NIK',
        'Pengguna',
        'Tgl Terima',
        'No. SO',
        'No. MOD',
        'Customer',
        'Kota / Kabupaten',
        'Tipe Transport',
        'Metode Hitung Ongkir',
        'Tipe Pengiriman',
        'Based On',
        'No. PO',
        'Ekspedisi',
        'Qty',
        'Harga/Kg',
        'Total',
        'No. APIN',
    ];


    public function collection()
    {
        $query_data = GoodScaleDetail::whereHas('goodScale',function($query){
            // $query->whereHas('journal',function($query){

            // });
            if($this->start_date && $this->finish_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->finish_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->finish_date) {
                $query->whereDate('post_date','<=', $this->finish_date);
            }

            if($this->status){
                $status = explode(',',$this->status);
                $query->whereIn('status', $status);
            }

            if($this->status_qc){
                $query->where('status_qc', $this->status_qc);
            }

            if($this->type){
                $arr = explode(',', $this->type);
                $query->whereIn('type', $arr);
            }
        })
        ->get();
        $arr = [];
        foreach($query_data as $key=>$row){
            $po_total = 0;
            if($row->goodScale->type == '1' || $row->goodScale->type =='3'){
                $no_sj = $row->goodScale->delivery_no ?? '-';
            }else{
                $no_sj = $row->lookable->marketingOrderDeliveryProcess->code ?? '-';


            }
            $po_code = '-';
            $list = '';
            if($row->goodScale->purchaseOrder()->exists()){
                $po_code =$row->goodScale->purchaseOrder->code;
                $list = $row->goodScale->purchaseOrder->getInvoice();
                $subtotal = $row->lookable->marketingOrderDeliveryProcess()->exists() ?$row->lookable->marketingOrderDeliveryProcess->purchaseOrderDetail->subtotal * $row->lookable->marketingOrderDeliveryProcess->purchaseOrderDetail->purchaseOrder->currency_rate : 0;
                $discount = $row->lookable->marketingOrderDeliveryProcess()->exists() ?$row->lookable->marketingOrderDeliveryProcess->purchaseOrderDetail->discountHeader() * $row->lookable->marketingOrderDeliveryProcess->purchaseOrderDetail->purchaseOrder->currency_rate : 0;
                $po_total = $subtotal-$discount;
            }


            $price = 0;
            $customer = $row->goodScale->account->name;
            $december16 = strtotime("2024-12-16 00:00:00");
            if($row->lookable->type_delivery != '1'){
                if (strtotime($row->goodScale->post_date) > $december16) {
                    $cost = $row->total;
                }else{
                    $cost = $row->total;
                }
                $price = $cost / ($row->qty == 0 ? 1 : $row->qty);
                $customer = $row->lookable->customer->name;
            }else{
                if (strtotime($row->goodScale->post_date) > $december16) {

                    $cost = $row->findProportionCost();
                }else{
                    $cost = $row->total;
                }
            }
            if($po_total == 0 || $po_total == null){
                $cost = 0;
            }
            if($row->lookable->type_delivery != 1){
                $arr[] = [
                    'no'                     => ($key+1),
                    'no_document'            => $row->goodScale->code,
                    'status'                 => $row->goodScale->statusRaw(),
                    'voider'                 => $row->goodScale->voidUser()->exists() ? $row->goodScale->voidUser->name : '',
                    'tgl_void'               => $row->goodScale->voidUser()->exists() ? date('d/m/Y', strtotime($row->goodScale->void_date)) : '',
                    'ket_void'               => $row->goodScale->voidUser()->exists() ? $row->goodScale->void_note : '',
                    'deleter'                => $row->goodScale->deleteUser()->exists() ? $row->goodScale->deleteUser->name : '',
                    'tgl_delete'             => $row->goodScale->deleteUser()->exists() ? date('d/m/Y', strtotime($row->goodScale->deleted_at)) : '',
                    'ket_delete'             => $row->goodScale->deleteUser()->exists() ? $row->goodScale->delete_note : '',
                    'nik'                    => $row->goodScale->user->employee_no,
                    'user'                   => $row->goodScale->user->name,
                    'tgl_terima'             => date('d/m/Y', strtotime($row->goodScale->post_date)),
                    'No. SO'                 => $row->lookable->getSO(),
                    'No. MOD'                 => $row->lookable->code,
                    'Customer'         => $customer,
                    'Kota / Kabupaten'          => $row->lookable->city->name ?? '-',
                    'Tipe Transport'                 => $row->lookable->transportation->name,
                    'Metode Hitung Ongkir'                  => $row->lookable->costDeliveryType(),
                    'Tipe Pengiriman'           => $row->lookable->deliveryType(),
                    'Based On'                => $no_sj,
                    'No. PO'              => $po_code,
                    'Ekspedisi'             => $row->goodScale->account->name,
                    'Qty'            => $row->qty,
                    'Harga'             => $price,
                    'Total'             => round($cost,2),
                    'No. APIN'             => $list,

                ];
            }

        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Good Scale X PO';
    }

    public function headings() : array
	{
		return $this->headings;
	}

    public function styles($sheet)
    {
        $rows = $this->collection();

        foreach ($rows as $key => $row) {
            // Check if there's no associated journal and qty is 0
            $hasJournal = $row['No. PO'] != '-' && $row['Qty'] > 0;

            // Apply styles to rows where journal is absent and qty is 0
            if (!$hasJournal) {
                $sheet->getStyle('A' . ($key + 2) . ':Z' . ($key + 2)) // Adjust for Excel row starting from 2
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB(Color::COLOR_RED);
            }
        }
    }
}
