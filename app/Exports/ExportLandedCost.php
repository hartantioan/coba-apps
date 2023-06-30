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

    public function __construct(string $search = null, string $status = null, string $vendor = null, string $currency = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->vendor = $vendor ? $vendor : '';
        $this->currency = $currency ? $currency : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'NO',
        'LC.NO',
        'PENGGUNA',
        'VENDOR',
        'PERUSAHAAN',
        'TGL.POST',
        'TGL.TENGGAT',
        'REFERENSI',
        'MATA UANG',
        'KONVERSI',
        'CATATAN',
        'TOTAL',
        'PPN',
        'PPh',
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
            
            if($this->currency){
                $arrCurrency = explode(',',$this->currency);
                $query->whereIn('currency_id',$arrCurrency);
            }
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->user->name,
                'vendor'        => $row->vendor->name,
                'company'       => $row->company->name,
                'tgl_post'      => $row->post_date,
                'tgl_due'       => $row->due_date,
                'ref'           => $row->reference,
                'mata_uang'     => $row->currency->code,
                'konversi'      => $row->currency_rate,
                'catatan'       => $row->note,
                'total'         => $row->total,
                'ppn'           => $row->tax,
                'pph'           => $row->wtax,
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
