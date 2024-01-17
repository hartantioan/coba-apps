<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ItemSerial extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_serials';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'lookable_type',
        'lookable_id',
        'item_id',
        'serial_number',
        'usable_type',
        'usable_id',
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function goodReceiptDetail(){
        if($this->lookable_type == 'good_receipt_details'){
            return $this->belongsTo('App\Models\GoodReceiptDetail', 'lookable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function usable(){
        return $this->morphTo();
    }

    public function goodIssueDetail(){
        if($this->usable_type == 'good_issue_details'){
            return $this->belongsTo('App\Models\GoodIssueDetail', 'usable_id', 'id')->withTrashed();
        }else{
            return $this->where('id',-1);
        }
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id')->withTrashed();
    }
}
