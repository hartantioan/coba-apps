<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryProcess;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageOrderDeliveryProcess implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$account_id,$company,$marketing_order,$end_date,$start_date,$dataplaces,$dataplacecode,$datawarehouses;


    public function __construct(string $search,string $status, string $account_id,string $company, string $marketing_order, string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->account_id = $account_id ? $account_id : '';
       
        $this->marketing_order = $marketing_order ? $marketing_order : '';
        $this->company = $company ? $company : '';
        
    }

    private $headings = [
        'No',
        'Kode',
        'Petugas',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'Tgl Posting',
        'Valid Date',
        'Perusahaan',
        'Customer',
        'Ekspedisi',
        'MOD',
        'Nama Supir',
        'No WA Supir',
        'Tipe Kendaraan',
        'Nopol Kendaraan',
        'Catatan Internal',
        'Catatan Eksternal',
        'Berat (KG)',
        'Tgl Kembali SJ',
        'Tracking',
        'Status',
    ];


    public function collection()
    {

        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $data = MarketingOrderDeliveryProcess::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('note_internal', 'like', "%$this->search%")
                        ->orWhere('note_external', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('account',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('marketingOrderDelivery',function($query) {
                            $query->where('code','like',"%$this->search%")
                                ->orWhereHas('marketingOrderDeliveryDetail',function($query) {
                                    $query->whereHas('item',function($query) {
                                        $query->where('code','like',"%$this->search%")
                                            ->orWhere('name','like',"%$this->search%");
                                        });
                                })->orWhereHas('customer',function($query){
                                    $query->where('name','like',"%$this->search%")
                                        ->orWhere('employee_no','like',"%$this->search%");
                                });
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

            if($this->account_id){
                $groupIds = explode(',', $this->account_id);
                $query->whereIn('account_id',$groupIds);
            }

            
            
            if($this->company){
                $query->where('company_id',$this->company);
            }
        })
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->get();
        
        
        $arr=[];
        foreach($data as $key => $row){
            
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->code,
                'petugas'           => $row->user->name,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'         => $row->voidUser()->exists() ? $row->void_date : '',
                'ket_void'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'ket_delete'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'tgl_done'         => $row->doneUser()->exists() ? $row->done_date : '',
                'ket_done'         => $row->doneUser()->exists() ? $row->done_note : '',
                'tgl_posting'         => date('d/m/Y',strtotime($row->post_date)),
                'valid_date'        => date('d/m/Y',strtotime($row->valid_date)),
                'perusahaan'           => $row->company->name,
                'ekspedisi'              => $row->account->name,
                'customer'              => $row->marketingOrderDelivery->customer->name,
                'mod'           =>  $row->marketingOrderDelivery->code,
                'nama_supir'        => $row->driver_name,
                'no_wa_supir'            => $row->driver_hp,
                'tipe_kendaraan'    => $row->vehicle_name,
                'nopol_kendaraan'     => $row->vehicle_no,
                'catatan_internal'            => $row->note_internal,
                'catatan_eksternal'      => $row->note_external,
                'berat_(kg)'      => $row->weight_netto,
                'tgl_kembali_sj'   =>  $row->return_date ? date('d/m/Y',strtotime($row->return_date)) : '-',
                'tracking'            =>  $row->statusTracking(),
                'status'            => $row->statusRaw(),
            ];
        
            
        }
        
        return collect($arr);
    }

    public function title(): string
    {
        return 'MOD';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
