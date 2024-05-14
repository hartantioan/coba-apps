<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use App\Helpers\CustomHelper;
use App\Models\MarketingOrder;

class ExportMarketingOrderTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'No. MO',
        'Status',
        'NIK',
        'Petugas',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Doner',
        'Tgl.Done',
        'Ket.Done',
        'Tgl.Posting',
        'Valid Date',
        'Customer',
        'Perusahaan',
        'Tipe',
        'Proyek',
        'Lampiran',
        'No Dokumen',
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
        'Catatan Internal',
        'Catatan Eksternal',
        'Subtotal',
        'Diskon',
        'Total',
        'PPN',
        'Total Setelah PPN',
        'Rounding',
        'Grandtotal',
        
    ];

    public function collection()
    {
        $data = MarketingOrder::where(function($query) {
            // Apply the search conditions within the 'purchaseOrder' relationship
            $query->where(function($query){
                $query->where('code', 'like', "%$this->search%")
                    ->orWhere('document_no', 'like', "%$this->search%")
                    ->orWhere('note', 'like', "%$this->search%")
                    ->orWhere('subtotal', 'like', "%$this->search%")
                    ->orWhere('discount', 'like', "%$this->search%")
                    ->orWhere('total', 'like', "%$this->search%")
                    ->orWhere('tax', 'like', "%$this->search%")
                    ->orWhere('grandtotal', 'like', "%$this->search%")
                    ->orWhereHas('user',function($query){
                        $query->where('name','like',"%$this->search%")
                            ->orWhere('employee_no','like',"%$this->search%");
                    })
                    ->orWhereHas('sales',function($query){
                        $query->where('name','like',"%$this->search%")
                            ->orWhere('employee_no','like',"%$this->search%");
                    })
                    ->orWhereHas('marketingOrderDetail',function($query) {
                        $query->whereHas('item',function($query){
                            $query->where('code','like',"%$this->search%")
                                ->orWhere('name','like',"%$this->search%");
                        });
                    });
            });
    
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
            $subtotal = $row->subtotal * $row->currency_rate;
            $discount = $row->discount * $row->currency_rate;
            $total = $subtotal - $discount;
            
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->code,
                'status'            => $row->statusRaw(),
                'nik'               => $row->user->employee_no,
                'user'              => $row->user->name,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'done_date'         => $row->doneUser()->exists() ? $row->done_date : '',
                'done_note'         => $row->doneUser()->exists() ? $row->done_note : '',
                'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                'valid_date'        => date('d/m/Y',strtotime($row->valid_date)),
                'customer'          => $row->account->name,
                'company'           => $row->company->name,
                'type'              => $row->type,
                'project'           => $row->project->name,
                'deliv_type'        => $row->type_delivery,
                'sender'            => $row->sender->name,
                'transport_type'    => $row->transport->name,
                'delivery_date'     => date('d/m/Y',strtotime($row->delivery_date)),
                'TOP_IN'            => $row->top_internal,
                'TOP_Customer'      => $row->top_customer,
                'is_guarantee'      => $row->is_guarantee,
                'billing_address'   => $row->billing_address,
                'outlet'            => $row->outlet->name,
                'destination_address'=> $row->destination_address,
                'province'          => $row->province->name,
                'city'              => $row->com_print_typeinfo->name,
                'district_id'       => $row->district->name,
                'subdistrict_id'    => $row->subdistrict->name,
                'sales'             => $row->sales->name,
                'currency_id'       => $row->currency->name,
                'currency_rate'     => number_format($row->currency_rate,2,',','.'),
                'dp'                => $row->percent_dp,
                'department'        => $row->note_internal,
                'warehouse'         => $row->note_external,
                'requester'         => number_format($row->subtotal,2,',','.'),
                'project'           => number_format($row->discount,2,',','.'),
                'price'             => number_format($row->total,2,',','.'),
                'conversion'        => number_format($row->tax,2,',','.'),
                'disc1'             => number_format($row->total_after_tax,2,',','.'),
                'disc2'             => number_format($row->rounding,2,',','.'),
                'disc3'             => number_format($row->grandtotal,2,',','.'),
            ];
        
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
