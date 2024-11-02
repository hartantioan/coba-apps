<?php

namespace App\Exports;

use App\Models\PurchaseRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportPurchaseRequestTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$start_date, $end_date, $status, $modedata, $warehouses;
    public function __construct(string $search ,string $start_date, string $end_date,string $status, string $modedata, array $warehouses)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? $status : '';
        $this->modedata = $modedata ? $modedata : '';
        $this->warehouses = $warehouses;
    }
    private $headings = [
        'No',
        'No. Dokumen',
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
        'Keterangan',
        'Kode Item',
        'Nama Item',
        'Plant',
        'Ket.1',
        'Ket.2',
        'Qty',
        'Satuan',
        'Qty.Konversi',
        'Satuan',
        'Tgl.Dipakai',
        'Line',
        'Mesin',
        'Divisi',
        'Gudang',
        'thiser',
        'Proyek',
        'Based On',
    ];
    public function collection()
    {
        $data = PurchaseRequestDetail::whereHas('purchaseRequest', function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        
                        ->orWhereHas('user',function($query){
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
        ->whereIn('warehouse_id',$this->warehouses)
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->purchaseRequest->code,
                'status'            => $row->purchaseRequest->statusRaw(),
                'voider'            => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->voidUser->name : '',
                'void_date'         => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->void_date : '',
                'void_note'         => $row->purchaseRequest->voidUser()->exists() ? $row->purchaseRequest->void_note : '',
                'deleter'           => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->deleteUser->name : '',
                'delete_date'       => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->deleted_at : '',
                'delete_note'       => $row->purchaseRequest->deleteUser()->exists() ? $row->purchaseRequest->delete_note : '',
                'doner'             => ($row->purchaseRequest->status == 3 && is_null($row->purchaseRequest->done_id)) ? 'sistem' : (($row->purchaseRequest->status == 3 && !is_null($row->purchaseRequest->done_id)) ? $row->purchaseRequest->doneUser->name : null),
                'doner_date'        => $row->purchaseRequest->doneUser()->exists() ? $row->purchaseRequest->done_date : '',
                'doner_note'        => $row->purchaseRequest->doneUser()->exists() ? $row->purchaseRequest->done_note : '',
                'post_date'         => date('d/m/Y',strtotime($row->purchaseRequest->post_date)),
                'user_code'         => $row->purchaseRequest->user->employee_no,
                'user_name'         => $row->purchaseRequest->user->name,
                'note'              => $row->purchaseRequest->note,
                'item_code'         => $row->item->code,
                'item_name'         => $row->item->name,
                'plant'             => $row->place()->exists() ? $row->place->code : '',
                'note1'             => $row->note,
                'note2'             => $row->note2,
                'qty'               => $row->qty,
                'unit'              => $row->itemUnit->unit->code,
                'qty_stock'         => $row->qty * $row->qty_conversion,
                'unit2'             => $row->item->uomUnit->code,
                'date_required'     => $row->required_date,
                'line'              => $row->line()->exists() ? $row->line->code : '',
                'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                'department'        => $row->department()->exists() ? $row->department->name : '',
                'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                'requester'         => $row->requester,
                'project'           => $row->project()->exists() ? $row->project->name : '',
                'reference'         => $row->lookable_id ? $row->lookable->materialRequest->code : '',
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap Purchase this';
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
