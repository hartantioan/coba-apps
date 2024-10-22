<?php

namespace App\Jobs;

use App\Exports\ExportReportStockInRupiahAccounting;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ExportStockInRupiahAccountingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $start_date;
    protected $place_id;
    protected $warehouse_id;
    protected $user_id;

    public function __construct($start_date, $place_id, $warehouse_id,$user_id)
    {
        $this->start_date = $start_date;
        $this->place_id = $place_id;
        $this->warehouse_id = $warehouse_id;
        $this->user_id = $user_id;
    }

    public function handle()
    {
        $filename = 'stock_in_rupiah_shading_' . uniqid() . '.xlsx';

        Excel::store(new ExportReportStockInRupiahAccounting($this->start_date, $this->place_id, $this->warehouse_id), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Stock Shading Accounting',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
