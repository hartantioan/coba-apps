<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ReceptionHardwareItemsUsage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'reception_hardware_items_usages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'hardware_item_id',
        'info',
        'date',
        'status',
        'status_item',
        'location',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
        'division',
        'reception_date',
        'return_date', 
        'user_return',
        'return_note',   
    ];

    public function status(){
        $status = match ($this->status) {
          '0' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Unassigned</span>',
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Penyerahan</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Pengembalian</span>',
          '3' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          '4' => '<span class="gradient-45deg-cyan-light-green medium-small white-text padding-3">Di Gudang || (Setelah Penyerahan)</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function statusRaw(){
        $status = match ($this->status) {
          '1' => 'Active',
          '2' => 'Not Active',
          '3' => '',
          '4' => 'Di Gudang',
          default => 'Invalid',
        };

        return $status;
    }

    public static function generateCode()
    {
        $query = ReceptionHardwareItemsUsage::selectRaw('RIGHT(code, 6) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return 'RHU'.$no;
    }

    public function hardwareItem(){
        return $this->belongsTo('App\Models\HardwareItem', 'hardware_item_id', 'id')->withTrashed();
    }


}
