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
        'Posting Date',
        'Document Number',
        'Account Code',
        'Account Name',
        'Document Date',
        'Received Date',
        'Due Date',
        'Cut Date',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
        'Downpayment',
        'Rounding',
        'Balance',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseInvoice::where( function($query) {
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }elseif($this->mode == '2'){
            $data = PurchaseInvoice::withTrashed()->where( function($query) {
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '2'                 => date('d/m/y',strtotime($row->post_date)),
                '3'                 => $row->code,
                '4'                 => $row->account->employee_no,
                '5'                 => $row->account->name,
                '6'                 => date('d/m/y',strtotime($row->document_date)),
                '7'                 => date('d/m/y',strtotime($row->received_date)),
                '8'                 => date('d/m/y',strtotime($row->due_date)),
                '11'                => date('d/m/y',strtotime($row->cut_date)),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'downpayment'       => number_format($row->downpayment,2,',','.'),
                '12'                => number_format($row->rounding,2,',','.'),
                '13'                => number_format($row->balance,2,',','.'),
                '14'                => $row->statusRaw(),
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
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
