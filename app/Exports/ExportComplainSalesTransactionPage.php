<?php

namespace App\Exports;

use App\Models\ComplaintSalesDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportComplainSalesTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date,$search,$status;

    public function __construct(string $search ,string $start_date, string $end_date,string $status)
    {
        $this->search = $search ? $search : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->status = $status ? $status : '';
    }
    private $headings = [
        'No.',
        'No. Dokumen',
        'Status',
        'Tgl.Posting',
        'Keterangan',
        'SO Berkaitan',
        'Ket. Komplain',
        'Solusi',
        'No. SJ',
        'Kode Item',
        'Nama Item',
        'Kode Batch Produksi',
        'Qty Salah Warna',
        'Qty Salah Motif',
        'Qty Salah Ukuran',
        'Qty Rusak',
        'Salah Qty',
        'Keterangan Detail',
    ];
    public function collection()
    {
        $array_filter = [];
        $mo = ComplaintSalesDetail::where( function ($query) {
            if($this->search) {
                $query->where(function($query)  {
                    $query->orWhereHas('complaintSale', function ($query) {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note_complaint', 'like', "%$this->search%")
                            ->orWhere('solution', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhere('post_date', 'like', "%$this->search%")
                            ->orWhere('complaint_date', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            })
                            ->orWhereHas('account',function($query) {
                                $query->where('name','like',"%$this->search%")
                                    ->orWhere('employee_no','like',"%$this->search%");
                            });
                    });
                });
            }

            if($this->start_date && $this->end_date) {
                $query->where(function($query)  {
                    $query->orWhereHas('complaintSale', function ($query) {
                        $query->whereDate('post_date', '>=', $this->start_date)
                        ->whereDate('post_date', '<=', $this->end_date);
                    });
                });
            } else if($this->start_date) {
                $query->where(function($query)  {
                    $query->orWhereHas('complaintSale', function ($query) {
                        $query->whereDate('post_date','>=', $this->start_date);
                    });
                });
            } else if($this->end_date) {
                $query->where(function($query)  {
                    $query->orWhereHas('complaintSale', function ($query) {
                        $query->whereDate('post_date','<=', $this->end_date);
                    });
                });
            }

            if($this->status){
                $array = explode(',', $this->status);
                $query->whereIn('status',$array);
            }
        })->get();


        foreach ($mo as $key=>$row) {

            $array_filter[] = [

                'No.' => $key+1,
                'No. Dokumen'=>$row->complaintSale->code,
                'Status'=>$row->complaintSale->statusRaw(),
                'Tgl.Posting'=>date('d/m/Y',strtotime($row->complaintSale->post_date)),
                'Keterangan'=>$row->complaintSale->statusRaw(),
                'SO Berkaitan'=>$row->complaintSale->marketingOrder?->code ?? '-',
                'Ket. Komplain'=>$row->complaintSale->note_complaint?? '-',
                'Solusi'=>$row->complaintSale->solution ?? '-',
                'No. SJ'=>$row->complaintSale->lookable->code,
                'Kode Item'=>$row->lookable->item->code,
                'Nama Item'=>$row->lookable->item->name,
                'Batch'=>$row->production_batch_code ?? '-',
                'Qty Salah Warna'=>$row->qty_color_mistake,
                'Qty Salah Motif'=>$row->qty_motif_mistake,
                'Qty Salah Ukuran'=>$row->qty_size_mistake,
                'Qty Rusak'=>$row->qty_broken,
                'Salah Qty'=>$row->qty_mistake,
                'Keterangan Detail'=>$row->note,

            ];
        }
        return collect($array_filter);
    }

    public function title(): string
    {
        return 'Export Komplain Sales';
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
