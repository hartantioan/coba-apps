<?php

namespace App\Exports;

use App\Models\LandedCost;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLandedCost implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $is_tax = null, string $is_include_tax = null, string $vendor = null, string $currency = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->is_tax = $is_tax ? $is_tax : '';
        $this->is_include_tax = $is_include_tax ? $is_include_tax : '';
        $this->vendor = $vendor ? $vendor : '';
        $this->currency = $currency ? $currency : '';
    }

    private $headings = [
        'NO',
        'LC.NO',
        'PENGGUNA',
        'VENDOR',
        'PO.NO',
        'GR.NO',
        'CABANG',
        'TGL.POST',
        'TGL.TENGGAT',
        'REFERENSI',
        'MATA UANG',
        'KONVERSI',
        'CATATAN',
        'TOTAL',
        'PAJAK',
        'GRANDTOTAL',
        'STATUS'
    ];

    public function collection()
    {
        $data = LandedCost::where(function ($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('reference', 'like', "%$this->search%")
                        ->orWhere('total', 'like', "%$this->search%")
                        ->orWhere('tax', 'like', "%$this->search%")
                        ->orWhere('grandtotal', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('landedCostDetail',function($query){
                            $query->whereHas('item',function($query){
                                $query->where('code', 'like', "%$this->search%")
                                    ->orWhere('name','like',"%$this->search%");
                            });
                        })
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('purchaseOrder',function($query){
                            $query->where('code','like',"%$this->search%");
                        })
                        ->orWhereHas('goodReceipt',function($query){
                            $query->where('code','like',"%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->vendor){
                $arrVendor = explode(',',$this->vendor);
                $query->whereIn('account_id',$arrVendor);
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
            
            if($this->currency){
                $arrCurrency = explode(',',$this->currency);
                $query->whereIn('currency_id',$arrCurrency);
            }
        })
        ->where('branch_id',session('bo_branch_id'))
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->user->name,
                'vendor'        => $row->vendor->name,
                'po'            => $row->purchaseOrder()->exists() ? $row->purchaseOrder->code : '-',
                'gr'            => $row->goodReceipt()->exists() ? $row->goodReceipt->code : '-',
                'cabang'        => $row->branch->name,
                'tgl_post'      => $row->post_date,
                'tgl_due'       => $row->due_date,
                'ref'           => $row->reference,
                'mata_uang'     => $row->currency->code,
                'konversi'      => $row->currency_rate,
                'catatan'       => $row->note,
                'total'         => $row->total,
                'pajak'         => $row->tax,
                'grandtotal'    => $row->grandtotal,
                'status'        => $row->statusRaw()
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Landed Cost';
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
