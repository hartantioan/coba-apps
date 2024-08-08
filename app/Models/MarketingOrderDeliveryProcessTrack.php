<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MarketingOrderDeliveryProcessTrack extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'marketing_order_delivery_process_tracks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'marketing_order_delivery_process_id',
        'note',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function marketingOrderDeliveryProcess()
    {
        return $this->belongsTo('App\Models\MarketingOrderDeliveryProcess', 'marketing_order_delivery_process_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => 'Dokumen SJ telah dibuat.',
          '2' => 'Barang telah dikirimkan.',
          '3' => 'Barang tiba di customer.',
          '4' => 'Barang selesai dibongkar.',
          '5' => 'Surat Jalan telah kembali.',
          default => 'Invalid',
        };

        return $status;
    }

    public function image(){
        $image = match ($this->status) {
          '1' => 'document.png',
          '2' => 'delivery.png',
          '3' => 'arrive.png',
          '4' => 'unload.png',
          '5' => 'returned.png',
          default => 'Invalid',
        };

        return $image;
    }

    public function printCounter()
    {
        return $this->hasMany('App\Models\PrintCounter','lookable_id','id')->where('lookable_type',$this->table);
    }
    public function isOpenPeriod(){
        $monthYear = substr($this->post_date, 0, 7); // '2023-02'

        // Query the LockPeriod model
        $see = LockPeriod::where('month', $monthYear)
                        ->whereIn('status_closing', ['2','3'])
                        ->get();
       
        if(count($see)>0){
            return true;
        }else{
            return false;
        }
    }
}
