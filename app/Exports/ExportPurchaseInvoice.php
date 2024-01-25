<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPurchaseInvoice implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    private $headings = [
        'No',
        'No.Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Tgl.Posting',
        'Tgl.Terima',
        'Tgl.Dokumen',
        'TOP',
        'Tgl.Jatuh Tempo',
        'No.Invoice',
        'No.Faktur Pajak',
        'No.Bukti Potong',
        'No.SPK',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'GR/LC/PO/Coa No.',
        'NO.PO/GRPO',
        'No. SJ',
        'Kode Item / COA',
        'Nama Item / COA',
        'Plant',
        'Qty',
        'Satuan',
        'Line',
        'Mesin',
        'Departemen',
        'Gudang',
        'Proyek',
        'Harga',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseInvoiceDetail::whereHas('purchaseInvoice',function($query){
                $query->where( function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                        ->where('post_date', '<=', $this->end_date);
                });
            })->get();
        }elseif($this->mode == '2'){
            $data = PurchaseInvoiceDetail::withTrashed()->whereHas('purchaseInvoice',function($query){
                $query->where( function($query) {
                    $query->where('post_date', '>=',$this->start_date)
                        ->where('post_date', '<=', $this->end_date);
                });
            })->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '3'                 => $row->purchaseInvoice->code,
                '4'                 => $row->purchaseInvoice->statusRaw(),
                'voider'            => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->voidUser->name : '',
                'void_date'         => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->void_date : '',
                'void_note'         => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->void_note : '',
                'deleter'           => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->deleteUser->name : '',
                'delete_date'       => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->deleted_at : '',
                'delete_note'       => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->delete_note : '',
                '6'                 => date('d/m/Y',strtotime($row->purchaseInvoice->post_date)),
                '7'                 => date('d/m/Y',strtotime($row->purchaseInvoice->received_date)),
                '8'                 => date('d/m/Y',strtotime($row->purchaseInvoice->document_date)),
                'top'               => $row->purchaseInvoice->top(),
                '11'                => date('d/m/Y',strtotime($row->purchaseInvoice->due_date)),
                '5'                 => $row->purchaseInvoice->invoice_no,
                'fp'                => $row->purchaseInvoice->tax_no,
                'fp_cut'            => $row->purchaseInvoice->tax_cut_no,
                'spk'               => $row->purchaseInvoice->spk_no,
                'supplier_code'     => $row->purchaseInvoice->account->employee_no,
                'supplier_name'     => $row->purchaseInvoice->account->name,
                'note'              => $row->purchaseInvoice->note,
                'code'              => $row->getHeaderCode(),
                'po_no'             => $row->getPurchaseCode(),
                'no_sj'             => $row->getDeliveryCode(),
                'item_code'         => $row->getCodeExport(),
                'item_name'         => $row->getNameExport(),
                'plant'             => $row->place->code,
                'qty'               => number_format($row->qty,3,',','.'),
                'unit'              => $row->getUnitConversion(),
                'line'              => $row->line()->exists() ? $row->line->code : '',
                'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                'department'        => $row->department()->exists() ? $row->department->name : '',
                'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                'project'           => $row->project()->exists() ? $row->project->name : '',
                'price'             => number_format($row->price,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
            ];
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap Purchase Invoice';
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

    // public function view(): View
    // {
    //     return view('admin.exports.purchase_invoice', [
    //         'data' => PurchaseInvoice::where(function($query) {
    //             if($this->search) {
    //                 $query->where(function($query) {
    //                     $query->where('code', 'like', "%$this->search%")
    //                         ->orWhere('total', 'like', "%$this->search%")
    //                         ->orWhere('tax', 'like', "%$this->search%")
    //                         ->orWhere('grandtotal', 'like', "%$this->search%")
    //                         ->orWhere('downpayment', 'like', "%$this->search%")
    //                         ->orWhere('balance', 'like', "%$this->search%")
    //                         ->orWhere('note', 'like', "%$this->search%")
    //                         ->orWhere('tax_no', 'like', "%$this->search%")
    //                         ->orWhere('tax_cut_no', 'like', "%$this->search%")
    //                         ->orWhere('spk_no', 'like', "%$this->search%")
    //                         ->orWhere('invoice_no', 'like', "%$this->search%")
    //                         ->orWhereHas('user',function($query){
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         })
    //                         ->orWhereHas('account',function($query){
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         })
    //                         ->orWhereHas('purchaseInvoiceDetail',function($query){
    //                             $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query){
    //                                 $query->where('code','like',"%$this->search%");
    //                             });
    //                         });
    //                 });
    //             }

    //             if($this->status){
    //                 $query->where('status', $this->status);
    //             }

    //             if($this->type){
    //                 $query->where('type',$this->type);
    //             }

    //             if($this->account){
    //                 $arrAccount = explode(',',$this->account);
    //                 $query->whereIn('account_id',$arrAccount);
    //             }

    //             if($this->company){
    //                 $query->where('company_id',$this->company);
    //             }
    //         })
    //         ->get()
    //     ]);
    // }
}
