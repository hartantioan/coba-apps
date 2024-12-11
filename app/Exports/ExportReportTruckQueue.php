<?php

namespace App\Exports;

use App\Models\TruckQueue;
use DateTime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportTruckQueue implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Status',
        'Kode',
        'User',
        'Supir',
        'No. pol',
        'Truk',
        'Tipe',
        'Kelengkapan Dokumen',
        'Kode Barcode',
        'Antri',
        'No Timbangan',
        'Timbang Masuk',
        'Muat FG',
        'Selesai Muat FG',
        'Lama Muat',
        'Timbang Keluar',
        'Kode SJ',
        'Keluar Pabrik',


    ];

    public function collection()
    {
        $mo = TruckQueue::where(function($query) {
            $query->where('date', '>=', $this->start_date)
                ->where('date', '<=', $this->finish_date);
        })->get();


        $arr = [];
        foreach ($mo as $key => $row) {
            $date1 = new DateTime($row->time_load_fg);
            $date2 = new DateTime($row->time_done_load_fg);
            $diff = $date1->diff($date2);
            $hours = $diff->h;
            $minutes = $diff->i;
            $diff_time = $hours.' jam '.$minutes.' menit';
            $gs_code="-";
            $gs_time_out="-";
            $sj_code="-";
            $gs_time_out="-";
            $sj_keluar="-";
            if($row->truckQueueDetail->goodScale()->exists()){
                $gs_code = $row->truckQueueDetail->goodScale->code;
                $gs_time_out=$row->truckQueueDetail->goodScale->time_scale_out;
                $sj_code = $row->truckQueueDetail->goodScale->getSalesSuratJalan();
                $gs_time_out=$row->truckQueueDetail->goodScale->time_scale_out;
                $sj_keluar=$row->truckQueueDetail->goodScale->getSuratJalanKeluarPabrik();
            }
            $arr[] = [
                'no'    => ($key+1),
                'status'=> $row->status(),
                'code'              => $row->code,
                'User'          => $row->user->name,
                'Supir' => $row->name,
                'No. pol'=> $row->no_pol,
                'Truk'=>$row->truck,
                'Tipe'=>$row->type(),
                'Kelengkapan Dokumen'=>$row->documentStatus(),
                'Kode Barcode'=>$row->code_barcode,
                'Antri'=>$row->date,
                'No Timbangan'=>$gs_code,
                'Timbang Masuk'=>$row->truckQueueDetail->time_in,
                'Muat FG'=>$row->time_load_fg,
                'Selesai Muat FG'=>$row->time_done_load_fg,
                'Lama Muat'=>$diff_time,
                'Timbang Keluar'=>$gs_time_out,
                'Kode SJ'=>$sj_code,
                'Keluar Pabrik'=>$sj_keluar,


            ];
        }

        return collect($arr);


    }

    public function title(): string
    {
        return 'Antrian Truck Report';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}
