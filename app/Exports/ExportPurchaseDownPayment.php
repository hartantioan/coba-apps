<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchaseDownPayment implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $type = null, string $place = null, string $department = null, string $is_tax = null, string $is_include_tax = null, string $supplier = null, string $currency = null,array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->place = $place ? $place : '';
        $this->department = $department ? $department : '';
        $this->is_tax = $is_tax ? $is_tax : '';
        $this->is_include_tax = $is_include_tax ? $is_include_tax : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'NO',
        'KODE',
        'PENGGUNA',
        'SUPPLIER',
        'TGL.POST',
        'TGL.TENGGAT',
        'TIPE',
        'MATA UANG',
        'KONVERSI',
        'KETERANGAN',
        'STATUS',
        'SUBTOTAL',
        'DISKON',
        'TOTAL',
        'PAJAK',
        'GRANDTOTAL'
    ];

    public function collection()
    {
        $data = PurchaseDownPayment::where(function ($query) {
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('grandtotal', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('purchaseDownPaymentDetail',function($query){
                            $query->whereHas('item',function($query){
                                $query->where('code', 'like', "%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                            });
                        })
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
                $query->where('type',$this->type);
            }

            if($this->supplier){
                $query->whereIn('account_id',$this->supplier);
            }
            
            if($this->place){
                $query->where('place_id',$this->place);
            }

            if($this->department){
                $query->where('department_id',$this->department);
            }            
            
            if($this->currency){
                $query->whereIn('currency_id',$this->currency);
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
        })
        ->whereIn('place_id',$this->dataplaces)
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->user->name,
                'supplier'      => $row->supplier->name,
                'post_date'     => $row->post_date,
                'due_date'      => $row->due_date,
                'tipe'          => $row->type(),
                'currency'      => $row->currency->code,
                'convert'       => $row->currency_rate,
                'catatan'       => $row->note,
                'status'        => $row->statusRaw(),
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
        return 'Laporan Purchase Down Payment';
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
