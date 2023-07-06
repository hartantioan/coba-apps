<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ReturnHardwareItemsUsage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'return_hardware_items_usages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'reception_hardware_item_usage_id',
        'date',
        'info',
        'status',
    ];

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public static function generateCode()
    {
        $query = ReturnHardwareItemsUsage::selectRaw('RIGHT(code, 6) as code')
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

        return 'RTHU'.$no;
    }

    public function receptionHardwareItem(){
        return $this->belongsTo('App\Models\ReceptionHardwareItemsUsage', 'reception_hardware_item_usage_id', 'id')->withTrashed();
    }
}
