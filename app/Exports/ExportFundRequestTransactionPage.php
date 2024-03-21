<?php

namespace App\Exports;

use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use App\Helpers\CustomHelper;

class ExportFundRequestTransactionPage implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell,ShouldAutoSize
{
    protected $search,$document,$start_date, $end_date, $status, $modedata;
    public function __construct(string $search,string $document,string $start_date, string $end_date,string $status, string $modedata)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status   = $status ? $status : '';
        $this->modedata = $modedata ? $modedata : '';
        $this->document = $document ? $document : '';
    }
    private $headings = [
        'No',
        'No. Dokumen',
        'Status',
        'Voider',
        'Tgl. Void',
        'Ket. Void',
        'Deleter',
        'Tgl. Delete',
        'Ket. Delete',
        'Pengguna',
        'Tgl. Posting',
        'Tgl. Req Pembayaran',
        'Partner Bisnis',
        'Tipe Permohonan',
        'Divisi',
        'Plant',
        'Keterangan',
        'Termin',
        'Tipe Pembayaran',
        'No. Rekening',
        'Rekening Penerima',
        'Bank Tujuan',
        'Deskripsi',
        'Qty.',
        'Satuan',
        'Harga',
        'Subtotal',
        'PPN',
        'PPh',
        'Total'
    ];

    public function collection()
    {
       
        $data = FundRequest::where(function($query) {
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

            if($this->document){
                $query->where('document_status', $this->document);
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
        ->get();
       

        $arr = [];

        foreach($data as $key => $row){
            foreach($row->fundRequestDetail as $rowDetail){
                $arr[] = [
                    'no'            => ($key + 1),
                    'no_dokumen'    => $row->code,
                    'status'        => $row->statusRaw(),
                    'voider'        => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'     => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'     => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'       => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'   => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'   => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'name'          => $row->user->name,
                    'post_date'     => $row->post_date,
                    'required_date' => $row->required_date,
                    'bussiness_partner'  => $row->account->name,
                    'type'          => $row->type(),
                    'division'      => $row->division()->exists() ? $row->division->name : '',
                    'company_id'    => $row->company->name,
                    'note'          => $row->note,
                    'termin_note'   => $row->termin_note,
                    'payment_type'  => $row->paymentType(),
                    'no_account'    => $row->no_account,
                    'name_account'  => $row->name_account,
                    'bank_account'  => $row->bank_account,
                    'dekripsi'      => $rowDetail->note,
                    'qty'           => CustomHelper::formatConditionalQty($rowDetail->qty),
                    'unit'          => $rowDetail->unit->name,
                    'harga'         => $rowDetail->price,
                    'subtotal'      => $rowDetail->total,
                    'ppn'           => $rowDetail->tax,
                    'pph'           => $rowDetail->wtax,
                    'grandtotal'    => $rowDetail->grandtotal,
                 
                    
                ];

            }
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Fund Request';
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
