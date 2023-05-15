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
        'place_id',
        'department_id',
        'warehouse_id',
    ];

    public function purchaseMemo()
    {
        return $this->belongsTo('App\Models\PurchaseMemo', 'purchase_memo_id', 'id')->withTrashed();
    }
    
    public function lookable(){
        return $this->morphTo();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }
}
