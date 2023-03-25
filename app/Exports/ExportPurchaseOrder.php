<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchaseOrder implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $type = null, string $shipping = null, string $place = null, string $department = null, string $is_tax = null, string $is_include_tax = null, string $payment = null, string $supplier = null, string $currency = null,array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->shipping = $shipping ? $shipping : '';
        $this->place = $place ? $place : '';
        $this->department = $department ? $department : '';
        $this->is_tax = $is_tax ? $is_tax : '';
        $this->is_include_tax = $is_include_tax ? $is_include_tax : '';
        $this->payment = $payment ? $payment : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'NO',
        'KODE',
        'PENGGUNA',
        'SUPPLIER',
        'TIPE',
        'SHIPPING',
        'PABRIK/KANTOR',
        'DEPARTEMEN',
        'DOK.REF',
        'PEMBAYARAN',
        'MATA UANG',
        'KONVERSI',
        'TGL.POST',
        'TGL.KIRIM',
        'NAMA PENERIMA',
        'ALAMAT PENERIMA',
        'TELEPON PENERIMA',
        'CATATAN',
        'SUBTOTAL',
        'DISKON',
        'TOTAL',
        'PAJAK',
        'GRANDTOTAL'
    ];

    public function collection()
    {
        $data = PurchaseOrder::where(function ($query) {
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('percent_tax', 'like', "%$this->search%")
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
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->type){
                $query->where('purchasing_type',$this->type);
            }

            if($this->shipping){
                $query->where('shipping_type',$this->shipping);
            }
            
            if($this->place){
                $query->where('place_id',$this->place);
            }

            if($this->department){
                $query->where('department_id',$this->department);
            }

            if($this->is_tax){
                if($this->is_tax == '1'){
                    $query->whereNotNull('is_tax');
                }else{
                    $query->whereNull('is_tax');
                }
            }

            if($this->is_include_tax){
                $query->where('is_include_tax',$this->is_include_tax);
            }

            if($this->payment){
                $query->where('payment_type',$this->payment);
            }

            if($this->supplier){
                $arrSupp = explode(',',$this->supplier);
                $query->whereIn('account_id',$arrSupp);
            }

            if($this->currency){
                $arrCurr = explode(',',$this->currency);
                $query->whereIn('currency_id',$arrCurr);
            }
        })
        ->whereIn('place_id',$this->dataplaces)
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->user->name,
                'supp'          => $row->supplier->name,
                'tipe'          => $row->purchasingType(),
                'ship'          => $row->shippingType(),
                'pabrik'        => $row->place->name.' - '.$row->place->company->name,
                'departemen'    => $row->department->name,
                'dok'           => $row->document_no,
                'pembayaran'    => $row->paymentType().' '.$row->payment_term.' hari',
                'mata_uang'     => $row->currency->code,
                'konversi'      => $row->currency_rate,
                'tgl_post'      => $row->post_date,
                'tgl_kirim'     => $row->delivery_date,
                'receiver_name' => $row->receiver_name,
                'receiver_add'  => $row->receiver_address,
                'receiver_phone'=> $row->receiver_phone,
                'catatan'       => $row->note,
                'subtotal'      => $row->subtotal,
                'diskon'        => $row->discount,
                'total'         => $row->total,
                'pajak'         => $row->tax,
                'grandtotal'    => $row->grandtotal
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
