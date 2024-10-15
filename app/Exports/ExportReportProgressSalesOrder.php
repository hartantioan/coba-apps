<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\MarketingOrder;

class ExportReportProgressSalesOrder implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search, $status,$type_sales, $type_pay,$type_deliv, $company, $customer, $delivery , $sales , $currency , $end_date , $start_date;

    public function __construct(string $search,string $status, string $type_sales,string $type_pay, string $type_deliv, string $company,string $customer, string $delivery, string $sales, string $currency,  string $end_date,string $start_date )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->type_sales = $type_sales ? $type_sales : '';

        $this->type_deliv = $type_deliv ? $type_deliv : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->customer = $customer ? $customer : '';
        $this->currency = $currency ? $currency : '';
        $this->delivery = $delivery ? $delivery : '';
        $this->sales = $sales ? $sales : '';

    }

    private $headings = [
        'No',
        'Item Code',
        'Item Name',
        'No. SO',
        'No. Referensi',
        'Tgl.Posting',
        'Status',
        'NIK',
        'Petugas',
        'Valid Date',
        'Customer',
        'Perusahaan',
        'Tipe',
        'Proyek',
        'Tipe Pengiriman',
        'Pengirim',
        'Tipe Transport',
        'Tgl Kirim',
        'Tipe Pembayaran',
        'TOP. Internal',
        'TOP. Customer',
        'Bergaransi',
        'Alamat Penagihan',
        'Outlet',
        'Alamat Tujuan',
        'Provinsi Tujuan',
        'Kota Tujuan',
        'Kecamatan Tujuan',
        'Kelurahan Tujuan',
        'Sales',
        'Mata Uang',
        'Konversi',
        '%DP',
        'Catatan Item',
        'Catatan Internal',
        'Catatan Eksternal',
        'Qty SO(M2)',//m2
        'Qty SO(Palet)',//m2
        'Qty SO(BOX)',//m2
        'Qty MOD(M2)',//m2
        'Qty MOD(Palet)',//m2
        'Qty MOD(BOX)',//m2
        'Qty SJ(M2)',//m2
        'Qty SJ(Palet)',//m2
        'Qty SJ(BOX)',//m2
    ];
    public function collection()
    {
        $data = MarketingOrder::where(function($query) {
            $query->whereIn('status', ['2','3']);

            // Other conditions for the 'purchaseOrder' relationship
            if($this->status){
                $groupIds = explode(',', $this->status);
                $query->whereIn('status', $groupIds);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

            if($this->type_sales){
                $query->where('type',$this->type_sales);
            }

            if($this->type_deliv){
                $query->where('shipping_type',$this->type_deliv);
            }

            if($this->customer){
                $groupIds = explode(',', $this->customer);
                $query->whereIn('account_id',$groupIds);
            }

            if($this->sales){
                $groupIds = explode(',', $this->sales);
                $query->whereIn('sales_id',$groupIds);
            }

            if($this->delivery){
                $groupIds = explode(',', $this->delivery);
                $query->whereIn('sender_id',$groupIds);
            }

            if($this->company){
                $query->where('company_id',$this->company);
            }

            if($this->type_pay){
                $query->where('payment_type',$this->type_pay);
            }

            if($this->currency){
                $groupIds = explode(',', $this->currency);
                $query->whereIn('currency_id',$groupIds);
            }


        })->get();


        foreach($data as $key => $row){

            foreach($row->marketingOrderDetail as $rowdetail){
                $qty_mod = 0;
                $qty_sj = 0;
                if($rowdetail->marketingOrderDeliveryDetail()->exists()){

                    foreach($rowdetail->marketingOrderDeliveryDetail as $row_mod_detail){
                        $qty_mod += $row_mod_detail->qty;
                        if($row_mod_detail->marketingOrderDeliveryProcessDetailWithPending()->exists()){
                            foreach($row_mod_detail->marketingOrderDeliveryProcessDetailWithPending as $row_sj){
                                $qty_sj += $row_sj->qty;
                            }
                        }
                    }
                }
                $qty_mod = $qty_mod*$rowdetail->qty_conversion;
                $qty_sj = $qty_sj*$rowdetail->qty_conversion;
                $qty_palet_so =round(($rowdetail->qty_uom/$rowdetail->item->sellConversion()),3);
                $qty_palet_mod = round(($qty_mod/$rowdetail->item->sellConversion()),3);
                $qty_palet_sj = round(($qty_sj/$rowdetail->item->sellConversion()),3);
                $qty_box_so=round(($rowdetail->qty_uom/$rowdetail->item->sellConversion())*$rowdetail->item->pallet->box_conversion,3);
                $qty_box_mod=round(($qty_mod/$rowdetail->item->sellConversion())*$rowdetail->item->pallet->box_conversion,3);
                $qty_box_sj=round(($qty_sj/$rowdetail->item->sellConversion())*$rowdetail->item->pallet->box_conversion,3);

                if($qty_palet_so == $qty_box_so){
                    $qty_palet_so = 0;
                }
                if($qty_palet_mod == $qty_box_mod){
                    $qty_palet_mod = 0;
                }
                if($qty_box_sj == $qty_palet_sj){
                    $qty_palet_sj = 0;
                }

                $arr[] = [
                    'no'                => ($key + 1),
                    'item_code'         => $rowdetail->item->code,
                    'item_name'         => $rowdetail->item->name,
                    'code'              => $row->code,
                    'reference'              => $row->document_no,
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'status'            => $row->statusRaw(),
                    'nik'               => $row->user->employee_no,
                    'employee'          => $row->user->name,
                    'valid_date'        => date('d/m/Y',strtotime($row->valid_date)),
                    'customer'          => $row->account->name,
                    'company'           => $row->company->name,
                    'type'              => $row->type(),
                    'project'           => $row->project->name ?? '-',
                    'deliv_type'        => $row->deliveryType(),
                    'sender'            => $row->sender()->exists() ? $row->sender->name : '-',
                    'transport_type'    => $row->transportation->name ?? '-',
                    'delivery_date'     => date('d/m/Y',strtotime($row->delivery_date)),
                    'payment_type'      => $row->paymentType(),
                    'TOP_IN'            => $row->top_internal,
                    'TOP_Customer'      => $row->top_customer,
                    'is_guarantee'      => $row->is_guarantee,
                    'billing_address'   => $row->billing_address,
                    'outlet'            => $row->outlet->name ?? '-',
                    'destination_address'=> $row->destination_address,
                    'province'          => $row->province->name  ?? '-',
                    'city'              => $row->com_print_typeinfo->name  ?? '-',
                    'district_id'       => $row->district->name  ?? '-',
                    'subdistrict_id'    => $row->subdistrict->name  ?? '-',
                    'sales'             => $row->sales->name  ?? '-',
                    'currency_id'       => $row->currency->name  ?? '-',
                    'currency_rate'     => number_format($row->currency_rate,2,',','.'),
                    'dp'                => $row->percent_dp,
                    'Catatan'              => $rowdetail->note,
                    'Catatan Internal'        => $row->note_internal,
                    'Catatan External'         => $row->note_external,
                    'qty_uom'           => round($rowdetail->qty_uom,3),
                    'qty_pallet'           => $qty_palet_so,
                    'qty_box'           => $qty_box_so,
                    'mod_qty_uom'           => round($qty_mod,3),
                    'mod_qty_pallet'           => $qty_palet_mod,
                    'mod_qty_box'           => $qty_box_mod,
                    'sj_qty_uom'           => round($qty_sj,3),
                    'sj_qty_pallet'           => $qty_palet_sj,
                    'sj_qty_box'           => $qty_box_sj,
                ];
            }
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Progress Sales Order';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
