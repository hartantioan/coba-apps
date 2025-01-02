<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TruckQueue extends Model
{
    use HasFactory, SoftDeletes;

    // Define the table name (if it's not the plural of the model name)
    protected $table = 'truck_queues';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    // Define the fillable properties to allow mass assignment
    protected $fillable = [
        'code',
        'name',
        'no_pol',
        'truck',
        'document_status',
        'code_barcode',
        'date',
        'status',
        'type',
        'user_id',
        'date',
        'time_load_fg',
        'time_done_load_fg',
        'expedition',

        'note',
        'no_container',
        'change_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function truckQueueDetail()
    {
        return $this->hasOne('App\Models\TruckQueueDetail');
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = TruckQueue::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
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

    public function status(){
        $status = match ($this->status) {
            '1' => 'ANTRI',
            '2' => 'TIMBANG MASUK',
            '3' => 'MUAT FG',
            '4' => 'SELESAI MUAT FG',
            '5' => 'TIMBANG KELUAR',
            '6' => 'KELUAR PABRIK',
            default => 'Invalid',
        };

        return $status;
    }

    public function type(){
        $status = match ($this->type) {
            '1' => 'Pembelian',
            '2' => 'Penjualan',
            '3' => 'Selesai',
            default => '-',
        };

        return $status;
    }

    public function documentStatus(){
        $status = match ($this->document_status) {
            '1' => 'LENGKAP',
            '2' => 'TIDAK LENGKAP',
            default => '-',
        };

        return $status;
    }
}
