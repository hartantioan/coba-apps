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

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';

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
        'Downpayment',
        'Cut Date',
        'Rounding',
        'Balance',
        'Status',
        
    ];

    public function collection()
    {
        $data = PurchaseInvoice::where( function($query) {
            $query->where('post_date', '>=',$this->start_date)
                  ->where('post_date', '<=', $this->end_date);
        })->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                => ($key + 1),
                '2'              => date('d/m/y',strtotime($row->post_date)),
                '3'                 => $row->code,
                '4'         => $row->account->employee_no,
                '5'          => $row->account->name,
                '6'              => date('d/m/y',strtotime($row->document_date)),
                '7'               => date('d/m/y',strtotime($row->received_date)),
                '8'              => date('d/m/y',strtotime($row->due_date)),
                '9'           => $row->downpayment,
                '11'         => date('d/m/y',strtotime($row->cut_date)),
                '12'              => $row->rounding,
                '13'              => $row->balance,
                '14'              => $row->statusRaw(),

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
