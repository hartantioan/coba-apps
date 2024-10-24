<?php

namespace App\Exports;

use App\Models\IncomingPayment;
use App\Models\MarketingOrderInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportARInvoicePaid implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStrictNullComparison
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'No. ARIN',
        'Status',
        'Voider',
        'Tanggal Void',
        'Keterangan Void',
        'Deleter',
        'Tanggal Delete',
        'Keterangan Delete',
        'Doner',
        'Tanggal Done',
        'Keterangan Done',
        'Tanggal Post',
        'Nominal Invoice',
        'Bank/COA',
        'Nominal dibayarkan',
        'DP/TOP',
        'Tgl IPYM',
        'No. IPYM',
        'Sisa Nominal Inv',
    ];

    public function collection()
    {
        $query_invoice= MarketingOrderInvoice::whereNull('deleted_at')
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->get();


        $arr = [];
        $keys= 1;
        foreach ($query_invoice as $row) {
            if($row->incomingPaymentDetail()->exists()){
                foreach($row->incomingPaymentDetail as $key=>$row_ip){
                    if($row_ip->incomingPayment->coa()->exists()){
                        $namecoa = $row_ip->incomingPayment->coa->code. '-' . $row_ip->incomingPayment->coa->name;
                    }else{
                        $namecoa = '';
                    }
                    $arr[] = [
                        'No'                =>$keys,
                        'No. ARIN'          => $row->code,
                        'Status'            =>$row->statusRaw(),
                        'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                        'tgl_void'          => $row->voidUser()->exists() ? $row->void_date : '',
                        'ket_void'          => $row->voidUser()->exists() ? $row->void_note : '',
                        'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                        'tgl_delete'        => $row->deleteUser()->exists() ? $row->deleted_at : '',
                        'ket_delete'        => $row->deleteUser()->exists() ? $row->delete_note : '',
                        'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                        'tgl_done'          => $row->doneUser()->exists() ? $row->done_date : '',
                        'ket_done'          => $row->doneUser()->exists() ? $row->done_note : '',
                        'tgl_posting'       => date('d/m/Y',strtotime($row->post_date)),
                        'Nominal Invoice'   =>$row->grandtotal,
                        'Bank/COA'          =>$namecoa,
                        'Nominal dibayarkan'=>$row_ip->total,
                        'DP/TOP'=>$row->downpayment,
                        'Tgl IPYM'=>date('d/m/Y',strtotime($row_ip->incomingPayment->post_date)),
                        'No. IPYM'=>$row_ip->incomingPayment->code,
                        'Sisa Nominal Inv'=>$row->balancePaymentIncoming(),
                    ];
                }
            }else{
                $arr[] = [
                    'No'                =>$keys,
                    'No. ARIN'          => $row->code,
                    'Status'            =>$row->statusRaw(),
                    'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'tgl_void'          => $row->voidUser()->exists() ? $row->void_date : '',
                    'ket_void'          => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'tgl_delete'        => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'ket_delete'        => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'tgl_done'          => $row->doneUser()->exists() ? $row->done_date : '',
                    'ket_done'          => $row->doneUser()->exists() ? $row->done_note : '',
                    'tgl_posting'       => date('d/m/Y',strtotime($row->post_date)),
                    'Nominal Invoice'   =>$row->grandtotal,
                    'Bank/COA'          =>'',
                    'Nominal dibayarkan'=>'',
                    'DP/TOP'=>$row->downpayment,
                    'Tgl IPYM'=>'',
                    'No. IPYM'=>'',
                    'Sisa Nominal Inv'=>$row->balancePaymentIncoming(),
                ];
            }


            $keys++;
        }



        return collect($arr);
    }

    public function title(): string
    {
        return 'Report AR Invoice Paid';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
