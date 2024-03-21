<?php

namespace App\Exports;
use App\Models\LandedCost;
use App\Models\LandedCostDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLandedCostTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$start_date, $end_date,$currency,$supplier, $status, $modedata;
    public function __construct(string $search ,string $start_date, string $end_date,string $currency,string $supplier,string $status, string $modedata)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? $status : '';
        $this->currency   = $currency ? $currency : '';
        $this->supplier   = $supplier ? $supplier : '';
        $this->modedata = $modedata ? $modedata : '';
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
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Kode Item',
        'Nama Item',
        'Plant',
        'Qty',
        'Satuan',
        'Total',
        'Based On',
    ];

    public function collection()
    {
        $data = LandedCostDetail::whereHas('landedCost',function ($query) {
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
                                $query->whereHas('item',function($query) {
                                    $query->where('code', 'like', "%$this->search%")
                                        ->orWhere('name','like',"%$this->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                }
                if($this->start_date && $this->end_date) {
                    $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                } else if($this->start_date) {
                    $query->whereDate('post_date','>=', $this->start_date);
                } else if($this->end_date) {
                    $query->whereDate('post_date','<=', $this->end_date);
                }

                if($this->status){
                    $groupIds = explode(',', $this->status);
                    $query->whereIn('status', $groupIds);
                }

                if($this->supplier){
                    $groupIds = explode(',', $this->supplier);
                    $query->whereIn('account_id',$groupIds);
                }
                

                if($this->currency){
                    $groupIds = explode(',', $this->currency);
                    $query->whereIn('currency_id',$groupIds);
                }

                if(!$this->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            ->get();

            $arr = [];

            foreach($data as $key => $row){
                $arr[] = [
                    'id'            => ($key + 1),
                    'code'          => $row->landedCost->code,
                    'status'        => $row->landedCost->statusRaw(),
                    'voider'        => $row->landedCost->voidUser()->exists() ? $row->landedCost->voidUser->name : '',
                    'void_date'     => $row->landedCost->voidUser()->exists() ? $row->landedCost->void_date : '',
                    'void_note'     => $row->landedCost->voidUser()->exists() ? $row->landedCost->void_note : '',
                    'deleter'       => $row->landedCost->deleteUser()->exists() ? $row->landedCost->deleteUser->name : '',
                    'delete_date'   => $row->landedCost->deleteUser()->exists() ? $row->landedCost->deleted_at : '',
                    'delete_note'   => $row->landedCost->deleteUser()->exists() ? $row->landedCost->delete_note : '',
                    'post_date'     => date('d/m/Y',strtotime($row->landedCost->post_date)),
                    'vendor_code'   => $row->landedCost->vendor->employee_no,
                    'vendor'        => $row->landedCost->vendor->name,
                    'note'          => $row->landedCost->note,
                    'item_code'     => $row->item->code,
                    'item_name'     => $row->item->name,
                    'place'         => $row->place->code,
                    'qty'           => $row->qty,
                    'unit'          => $row->item->uomUnit->code,
                    'total'         => number_format($row->nominal,2,',','.'),
                    'based_on'      => $row->landedCost->getReference(),
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
