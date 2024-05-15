<?php

namespace App\Exports;

use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class ExportPurchaseOrderTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $status, $type_buy,$type_deliv, $company, $type_pay,$supplier, $currency, $end_date, $start_date , $search , $modedata;

    public function __construct(string $search,string $status, string $type_buy,string $type_deliv, string $company, string $type_pay,string $supplier, string $currency, string $end_date, string $start_date,  string $modedata )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->type_buy = $type_buy ? $type_buy : '';
       
        $this->type_deliv = $type_deliv ? $type_deliv : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->currency = $currency ? $currency : '';
        $this->modedata = $modedata ? $modedata : '';
        
    }
    private $headings = [
        'No',
        'No. PO',
        'Status',
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
        'NIK',
        'User',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Nomor Dokumen',
        'Tipe Pembelian',
        'Tgl.Kirim',
        'Tgl.Terima',
        'Kode Item',
        'Nama Item',
        'Plant',
        'Ket.1',
        'Ket.2',
        'Qty',
        'Satuan',
        'Qty.Konversi',
        'Satuan',
        'Line',
        'Mesin',
        'Divisi',
        'Gudang',
        'Requester',
        'Proyek',
        'Harga',
        'Konversi',
        'Disc1',
        'Disc2',
        'Disc3',
        'Subtotal',
        'Diskon PO',
        'Total',
        'Based On'
    ];

    public function collection()
    {
        $data = PurchaseOrderDetail::whereHas('purchaseOrder', function($query) {
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
                    ->orWhereHas('supplier',function($query){
                        $query->where('name','like',"%$this->search%")
                            ->orWhere('employee_no','like',"%$this->search%");
                    })
                    ->orWhereHas('purchaseOrderDetail',function($query) {
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
    
            if($this->type_buy){
                $query->where('inventory_type',$this->type_buy);
            }
    
            if($this->type_deliv){
                $query->where('shipping_type',$this->type_deliv);
            }
    
            if($this->supplier){
                $groupIds = explode(',', $this->supplier);
                $query->whereIn('account_id',$groupIds);
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
    
            if(!$this->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->get();
    

        foreach($data as $key => $row){
            $subtotal = $row->subtotal * $row->purchaseOrder->currency_rate;
            $discount = $row->discountHeader() * $row->purchaseOrder->currency_rate;
            $total = $subtotal - $discount;
            if($row->item()->exists()){
                $arr[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->purchaseOrder->code,
                    'status'            => $row->purchaseOrder->statusRaw(),
                    'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                    'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                    'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                    'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                    'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                    'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                    'doner'             => ($row->purchaseOrder->status == 3 && is_null($row->purchaseOrder->done_id)) ? 'sistem' : (($row->purchaseOrder->status == 3 && !is_null($row->purchaseOrder->done_id)) ? $row->purchaseOrder->doneUser->name : null),
                    'done_date'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_date : '',
                    'done_note'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->purchaseOrder->post_date)),
                    'user_code'         => $row->purchaseOrder->user->employee_no,
                    'user_name'         => $row->purchaseOrder->user->name,
                    'supplier_code'     => $row->purchaseOrder->supplier->employee_no,
                    'supplier_name'     => $row->purchaseOrder->supplier->name,
                    'main_note'         => $row->purchaseOrder->note,
                    'document_no'       => $row->purchaseOrder->document_no,
                    'type'              => $row->purchaseOrder->inventoryType(),
                    'delivery_date'     => date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)),
                    'received_date'     => $row->purchaseOrder->received_date ? date('d/m/Y',strtotime($row->purchaseOrder->received_date)) : '',
                    'item_code'         => $row->item->code,
                    'item_name'         => $row->item->name,
                    'plant'             => $row->place()->exists() ? $row->place->code : '',
                    'note'              => $row->note,
                    'note2'             => $row->note2,
                    'qty'               => $row->qty,
                    'unit'              => $row->itemUnit->unit->code,
                    'qty_stock'         => $row->qty * $row->qty_conversion,
                    'unit_stock'        => $row->item->uomUnit->code,
                    'line'              => $row->line()->exists() ? $row->line->code : '',
                    'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                    'department'        => $row->department()->exists() ? $row->department->name : '',
                    'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                    'requester'         => $row->requester,
                    'project'           => $row->project()->exists() ? $row->project->name : '',
                    'price'             => $row->price,
                    'conversion'        => number_format($row->purchaseOrder->currency_rate,2,',','.'),
                    'disc1'             => number_format($row->percent_discount_1,2,',','.'),
                    'disc2'             => number_format($row->percent_discount_2,2,',','.'),
                    'disc3'             => number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.'),
                    'subtotal'          => number_format($subtotal,2,',','.'),
                    'discount'          => number_format($discount,2,',','.'),
                    'total'             => number_format($total,2,',','.'),
                    'based_on'          => $row->getReference(),
                ];
            }else{
                $arr[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->purchaseOrder->code,
                    'status'            => $row->purchaseOrder->statusRaw(),
                    'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                    'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                    'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                    'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                    'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                    'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                    'doner'             => ($row->purchaseOrder->status == 3 && is_null($row->purchaseOrder->done_id)) ? 'sistem' : (($row->purchaseOrder->status == 3 && !is_null($row->purchaseOrder->done_id)) ? $row->purchaseOrder->doneUser->name : null),
                    'done_date'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_date : '',
                    'done_note'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->purchaseOrder->post_date)),
                    'user_code'         => $row->purchaseOrder->user->employee_no,
                    'user_name'         => $row->purchaseOrder->user->name,
                    'supplier_code'     => $row->purchaseOrder->supplier->employee_no,
                    'supplier_name'     => $row->purchaseOrder->supplier->name,
                    'main_note'         => $row->purchaseOrder->note,
                    'document_no'       => $row->purchaseOrder->document_no,
                    'type'              => $row->purchaseOrder->inventoryType(),
                    'delivery_date'     => date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)),
                    'received_date'     => $row->purchaseOrder->received_date ? date('d/m/Y',strtotime($row->purchaseOrder->received_date)) : '',
                    'item_code'         => $row->coa->code,
                    'item_name'         => $row->coa->name,
                    'plant'             => $row->place()->exists() ? $row->place->code : '',
                    'note'              => $row->note,
                    'note2'             => $row->note2,
                    'qty'               => 1,
                    'unit'              => $row->itemUnit()->exists() ? $row->itemUnit->unit->code : ($row->coaUnit()->exists() ? $row->coaUnit->code : '-'),
                    'qty_stock'         => 1,
                    'unit_stock'        => '-',
                    'line'              => $row->line()->exists() ? $row->line->code : '',
                    'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                    'department'        => $row->department()->exists() ? $row->department->name : '',
                    'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                    'requester'         => $row->requester,
                    'project'           => $row->project()->exists() ? $row->project->name : '',
                    'price'             => $row->price,
                    'conversion'        => number_format($row->purchaseOrder->currency_rate,2,',','.'),
                    'disc1'             => number_format($row->percent_discount_1,2,',','.'),
                    'disc2'             => number_format($row->percent_discount_2,2,',','.'),
                    'disc3'             => number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.'),
                    'subtotal'          => number_format($subtotal,2,',','.'),
                    'discount'          => number_format($discount,2,',','.'),
                    'total'             => number_format($total,2,',','.'),
                    'based_on'          => $row->getReference(),
                ];
            }
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap Purchase Order';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
