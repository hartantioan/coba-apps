<?php

namespace App\Jobs;

use App\Exports\ExportStockMovement;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class StockMovementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;

    protected $user_id;
    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , string $group ,string $user_id)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
        $this->user_id = $user_id;
        $this->queue = 'report';
    }

    public function handle()
    {
        $filename = 'stock_movement_' . uniqid() . '.xlsx';

        Excel::store(new ExportStockMovement($this->plant,$this->item,$this->warehouse,$this->start_date,$this->finish_date,$this->type,$this->group), 'public/report/'.$filename);
        Notification::create([
            'code'				=> Str::random(20),
            'menu_id'			=> 0,
            'from_user_id'		=> $this->user_id,
            'to_user_id'		=> $this->user_id,
            'lookable_type'		=> 'report',
            'lookable_id'		=> 0,
            'title'				=> 'Report telah berhasil diproses Stock Movement Mohon Ditunggu',
            'note'				=> env('APP_URL').'/storage/report/'.$filename,
            'status'			=> '1'
        ]);
    }
}
