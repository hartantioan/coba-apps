<?php

namespace App\Exports;

use App\Models\IncomingPayment;
use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportIncomingPayment implements FromView,ShouldAutoSize
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

    public function view(): View
    {
        if($this->mode == '1'){
            $data = IncomingPayment::where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new IncomingPayment())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Incoming payment data.');
            return view('admin.exports.incoming_payment', [
                'data' => $data
            ]);
        }elseif($this->mode == '2'){
            $data = IncomingPayment::withTrashed()->where(function($query){
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
            activity()
                ->performedOn(new IncomingPayment())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Incoming payment data.');
            return view('admin.exports.incoming_payment', [
                'data' => $data
            ]);
        }
    }
}

// class ExportIncomingPayment implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
// {
//     protected $start_date, $end_date, $mode;

//     public function __construct(string $start_date, string $end_date, string $mode)
//     {
//         $this->start_date = $start_date ? $start_date : '';
// 		$this->end_date = $end_date ? $end_date : '';
//         $this->mode = $mode ? $mode : '';
//     }

//     private $headings = [
//         'No.',
//         'No. Dokumen',
//         'Status',
//         'Voider',
//         'Tgl. Void',
//         'Ket. Void',
//         'Deleter',
//         'Tgl. Delete',
//         'Ket. Delete',
//         'NIK',
//         'User',
//         'Bussiness Partner',
//         'Post Date',
//         'Kas/Bank',
//         'Note',
//         'Subtotal',
//         'Pembulatan',
//         'Total',
//         'Ket. Detail',
//         'Based On'
//     ];

//     public function collection()
//     {
//         $arr = [];
//         if($this->mode == '1'){
//             $data = IncomingPayment::where(function($query){
//                 $query->where('post_date', '>=',$this->start_date)
//                 ->where('post_date', '<=', $this->end_date);
//             })
//             ->get();
//             activity()
//                 ->performedOn(new IncomingPayment())
//                 ->causedBy(session('bo_id'))
//                 ->withProperties(null)
//                 ->log('Export Incoming payment data.');
//         }elseif($this->mode == '2'){
//             $data = IncomingPayment::withTrashed()->where(function($query){
//                 $query->where('post_date', '>=',$this->start_date)
//                 ->where('post_date', '<=', $this->end_date);
//             })
//             ->get();
//             activity()
//                 ->performedOn(new IncomingPayment())
//                 ->causedBy(session('bo_id'))
//                 ->withProperties(null)
//                 ->log('Export Incoming payment data.');
//         }
//         $no = 1;
//         foreach($data as $row_ip){
//             foreach($row_ip->incomingPaymentDetail as $rowDetail){
//                 $arr[] = [
//                     'No.' => $no, // Assuming $key is the loop index
//                     'No. Dokumen' => $row_ip->code,
//                     'Status' => $row_ip->statusRaw(),
//                     'Voider' => $row_ip->voidUser()->exists() ? $row_ip->voidUser->name : '',
//                     'Tgl. Void' => $row_ip->voidUser()->exists() ? date('d/m/Y', strtotime($row_ip->void_date)) : '',
//                     'Ket. Void' => $row_ip->voidUser()->exists() ? $row_ip->void_note : '',
//                     'Deleter' => $row_ip->deleteUser()->exists() ? $row_ip->deleteUser->name : '',
//                     'Tgl. Delete' => $row_ip->deleteUser()->exists() ? date('d/m/Y', strtotime($row_ip->deleted_at)) : '',
//                     'Ket. Delete' => $row_ip->deleteUser()->exists() ? $row_ip->delete_note : '',
//                     'NIK' => $row_ip->user->employee_no,
//                     'User' => $row_ip->user->name,
//                     'Bussiness Partner' => $row_ip->account->name ?? '-',
//                     'Post Date' => $row_ip->post_date,
//                     'Kas/Bank' => $row_ip->coa->name ?? '-',
//                     'Note' => $row_ip->note,
//                     'Subtotal' => $rowDetail->subtotal,
//                     'Pembulatan' => $rowDetail->rounding,
//                     'Total' => $rowDetail->total,
//                     'Ket. Detail' => $rowDetail->note,
//                     'Based On' => $rowDetail->getCode() ?? '-',
//                 ];
//                 $no++;
//             }

//         }


//         return collect($arr);


//     }

//     public function title(): string
//     {
//         return 'Report Incoming Payment';
//     }

//     public function headings() : array
// 	{
// 		return $this->headings;
// 	}
// }
