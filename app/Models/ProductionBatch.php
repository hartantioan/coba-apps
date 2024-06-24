<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_batches';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'item_id',
        'lookable_type',
        'lookable_id',
        'qty'
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function productionBatchUsage()
    {
        return $this->hasMany('App\Models\ProductionBatchUsage');
    }

    public function item(){
        return $this->belongsTo('App\Models\Item','item_id','id');
    }

    public static function generateCode($type,$line,$group){
        $newcode = '';
        if($type == 'normal'){
            $newcode .= 'PD'.date('y');
        }elseif($type == 'powder'){
            $newcode .= 'PW'.date('y');
        }
        $query = ProductionBatch::selectRaw('SUBSTRING(code,5,8) as code')
            ->whereRaw("code LIKE '$newcode%'")
            ->orderByDesc('id')
            ->limit(1)
            ->get();
        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return $newcode.$no.'.'.strtoupper($line).strtoupper($group);
    }
}