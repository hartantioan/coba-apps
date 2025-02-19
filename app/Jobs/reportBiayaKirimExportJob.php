<?php

namespace App\Jobs;

use App\Exports\ExportDeliveryCost;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class reportBiayaKirimExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $start_date, $end_date,$search,$status,$account,$user_id;


    public function __construct(string $search ,string $start_date, string $end_date,string $status,string $account,string $user_id)
    {
        $this->search = $search ? $search : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->status = $status ? $status : '';
        $this->account = $account ? $account : '';
        $this->user_id = $user_id;
        $this->queue = 'report';
    }

    public function handle()
    {
        $filename = 'delivery_cost_' . uniqid() . '.xlsx';

        Excel::store(new ExportDeliveryCost($this->search,$this->start_date,$this->end_date,$this->status,$this->account), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Biaya Kirim bisa di download',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
