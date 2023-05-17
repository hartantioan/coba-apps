<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PurchaseMemoDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'purchase_memo_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'purchase_memo_id',
        'lookable_type',
        'lookable_id',
        'description',
        'total',
        'tax',
        'wtax',
        'grandtotal',
    ];

    public function purchaseMemo()
    {
        return $this->belongsTo('App\Models\PurchaseMemo', 'purchase_memo_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }
}
