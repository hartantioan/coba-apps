<?php

namespace App\Exports;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class ExportPurchaseOrder implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $inventory = null, string $type = null, string $shipping = null, string $company = null, string $is_tax = null, string $is_include_tax = null, string $payment = null, string $supplier = null, string $currency = null,array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->inventory = $inventory ? $inventory : '';
        $this->type = $type ? $type : '';
        $this->shipping = $shipping ? $shipping : '';
        $this->company = $company ? $company : '';
        $this->is_tax = $is_tax ? $is_tax : '';
        $this->is_include_tax = $is_include_tax ? $is_include_tax : '';
        $this->payment = $payment ? $payment : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    public function sheets(): array
    {
        $purchaseorder = PurchaseOrder::where(function ($query) {
            if($this->search) {
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
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->inventory){
                $query->where('inventory_type',$this->inventory);
            }

            if($this->type){
                $query->where('purchasing_type',$this->type);
            }

            if($this->shipping){
                $query->where('shipping_type',$this->shipping);
            }
            
            if($this->company){
                $query->where('company_id',$this->company);
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
        ->get([
            'id',
            'code',
            'user_id',
            'account_id',
            'inventory_type',
            'purchasing_type',
            'shipping_type',
            'company_id',
            'document_no',
            'document_po',
            'payment_type',
            'payment_term',
            'currency_id',
            'currency_rate',
            'post_date',
            'delivery_date',
            'note',
            'subtotal',
            'discount',
            'total',
            'tax',
            'wtax',
            'grandtotal',
            'status',
            'void_id',
            'void_note',
            'void_date',
            'receiver_name',
            'receiver_address',
            'receiver_phone'
        ]);

        $sheets = [];

        $sheets[] = new PurchaseOrderSheet($purchaseorder);

        $purchaseorderdetail = PurchaseOrderDetail::whereIn('purchase_order_id', $purchaseorder->pluck('id')->toArray())->get([
            'purchase_order_id',
            'item_id',
            'coa_id',
            'qty',
            'price',
            'percent_discount_1',
            'percent_discount_2',
            'discount_3',
            'subtotal',
            'note',
            'is_tax',
            'is_include_tax',
            'percent_tax',
            'is_wtax',
            'percent_wtax'
        ]);
        $sheets[] = new PurchaseOrderDetailSheet($purchaseorderdetail);


        return $sheets;
    }
}

class PurchaseOrderSheet implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    private $purchaseorder;

    public function __construct(Collection $purchaseorder)
    {
        $this->purchaseorder = $purchaseorder;
    }

    public function collection()
    {
        $arr = new Collection();

        foreach($this->purchaseorder as $key => $row){
            $arr->push([
                ($key + 1),
                $row->code,
                $row->user->name,
                $row->supplier->name,
                $row->inventoryType(),
                $row->purchasingType(),
                $row->shippingType(),
                $row->company->name,
                $row->document_no,
                $row->paymentType().' '.$row->payment_term.' hari',
                $row->currency->code,
                $row->currency_rate,
                $row->post_date,
                $row->delivery_date,
                $row->receiver_name,
                $row->receiver_address,
                $row->receiver_phone,
                $row->note,
                $row->subtotal,
                $row->discount,
                $row->total,
                $row->tax,
                $row->wtax,
                $row->grandtotal,
            ]);
        }

        return $arr;
    }

    public function title(): string
    {
        return 'Laporan Purchase Request';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE',
            'PENGGUNA',
            'SUPPLIER',
            'TIPE PO',
            'JENIS PO',
            'SHIPPING',
            'PERUSAHAAN',
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
            'PPN',
            'PPH',
            'GRANDTOTAL'
        ];
    }
}

class PurchaseOrderDetailSheet implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    private $purchaseorderdetail;

    public function __construct(Collection $purchaseorderdetail)
    {
        $this->purchaseorderdetail = $purchaseorderdetail;
    }

    public function collection()
    {
        $arr = new Collection();

        foreach($this->purchaseorderdetail as $key => $row){
            $arr->push([
                ($key + 1),
                $row->purchaseOrder->code,
                $row->item_id ? $row->item->name : '',
                $row->coa_id ? $row->coa->name : '',
                $row->qty,
                $row->price,
                $row->percent_discount_1,
                $row->percent_discount_2,
                $row->discount_3,
                $row->subtotal,
                $row->note,
                $row->isTax(),
                $row->isIncludeTax(),
                $row->percent_tax,
                $row->isWtax(),
                $row->percent_wtax,
            ]);
        }

        return $arr;
    }

    public function title(): string
    {
        return 'Detail Purchase Order';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return [
            'NO',
            'KODE',
            'NAMA ITEM',
            'NAMA JASA',
            'QTY',
            'PRICE',
            'DISKON 1(%)',
            'DISKON 2(%)',
            'DISKON 3(%)',
            'SUBTOTAL',
            'KETERANGAN',
            'MERUPAKAN PAJAK',
            'TERMASUK PAJAK',
            'PERSEN PAJAK',
            'APAKAH WTAX',
            'PERSEN WTAX',
        ];
    }
}
