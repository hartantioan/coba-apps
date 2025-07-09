<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostOfGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cost_of_goods';

    protected $fillable = [
        'code',
        'user_id',
        'item_id',
        'date',
        'price',
        'discount',
        'status',
    ];

    protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->code)) {
            $model->code = self::generateCode();
        }
    });
}

public static function generateCode()
{
    $prefix = 'COG' . date('ym'); // Example: COG2507

    $query = self::selectRaw('RIGHT(code, 8) as code')
        ->whereRaw("code LIKE '{$prefix}%'")
        ->withTrashed()
        ->orderByDesc('code')
        ->orderByDesc('id')
        ->first();

    if ($query && is_numeric($query->code)) {
        $code = (int)$query->code + 1;
    } else {
        $code = 1;
    }

    $no = str_pad($code, 8, '0', STR_PAD_LEFT);

    return $prefix . '-' . $no;
}


    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}
