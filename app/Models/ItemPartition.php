<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPartition extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_partitions';

    protected $fillable = [
        'code',
        'user_id',
        'post_date',
        'note',
        'document',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date',
        'delete_id',
        'delete_note',
        'done_id',
        'done_date',
        'done_note',
        'post_date',
        'void_date',
        'done_date',
    ];

    public function itemPartitionDetail()
    {
        return $this->hasMany('App\Models\ItemPartitionDetail','item_partition_id','id');
    }
    public static function generateCode($prefix)
    {
        $query = ItemPartition::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$prefix%'")
            ->withTrashed()
            ->orderByDesc('code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    // Optional relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          1 => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          2 => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          3 => 'Selesai',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };
        info($this->status);
        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Direvisi',
            '7' => 'Schedule',
            '8' => 'Ditutup Balik',
            default => 'Invalid',
        };
        return $status;
    }
    public function voidUser()
    {
        return $this->belongsTo(User::class, 'void_id')->withTrashed();
    }

    public function deleteUser()
    {
        return $this->belongsTo(User::class, 'delete_id')->withTrashed();
    }

    public function doneUser()
    {
        return $this->belongsTo(User::class, 'done_id')->withTrashed();
    }
}
