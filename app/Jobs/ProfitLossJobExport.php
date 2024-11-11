<?php

namespace App\Jobs;

use App\Exports\ExportProfitLoss;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ProfitLossJobExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month_start,$month_end,$level,$company, $user_id;


    public function __construct(string $month_start, string $month_end,string $level,string $company, string $user_id)
    {
        $this->month_start = $month_start ? $month_start : '';
		$this->month_end = $month_end ? $month_end : '';
        $this->level = $level ? $level : '';
        $this->company = $company ? $company : '';
        $this->user_id = $user_id;
        $this->queue = 'report';
    }

    public function handle()
    {
        $filename = 'profit_loss_' . uniqid() . '.xlsx';

        Excel::store(new ExportProfitLoss($this->month_start,$this->month_end,$this->level,$this->company), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Profit LOSS Mohon Ditunggu',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
