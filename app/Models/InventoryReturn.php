<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReturn extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_returns';

    protected $fillable = [
        'code',
        'user_id',
        'note',
        'post_date',
        'grandtotal',
        'status',
        'void_id',
        'void_note',
        'void_date',
    ];

    public function inventoryReturnDetail()
    {
        return $this->hasMany(InventoryReturnDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'void_id');
    }

    public function deleteUser()
    {
        return $this->belongsTo('App\Models\User', 'delete_id', 'id')->withTrashed();
    }

    public function used(){
        return $this->hasOne('App\Models\UsedData','lookable_id','id')->where('lookable_type',$this->table);
    }

    public static function generateCode($prefix)
    {
        $query = InventoryReturn::selectRaw('RIGHT(code, 8) as code')
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

    public function status() {
        $status = match ((string)$this->status) {
            '1' => '<span class="amber medium-small white-text padding-3">Menunggu</span>',
            '2' => '<span class="cyan medium-small white-text padding-3">Proses</span>',
            '3' => '<span class="green medium-small white-text padding-3">Selesai</span>',
            '4' => '<span class="red medium-small white-text padding-3">Ditolak</span>',
            '5' => '<span class="red darken-4 medium-small white-text padding-3">Ditutup</span>',
            '6' => '<span class="yellow darken-4 medium-small white-text padding-3">Revisi</span>',
            default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

}
