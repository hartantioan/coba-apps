<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'assets';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'place_id',
        'name',
        'asset_group_id',
        'date',
        'nominal',
        'method',
        'cost_coa_id',
        'note',
        'status',
        'book_balance',
    ];

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function method(){
        $method = match ($this->method) {
          '1' => 'Straight Line',
          '2' => 'Declining Balance',
          default => 'Invalid',
        };

        return $method;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }
    public function assetGroup()
    {
        return $this->belongsTo('App\Models\AssetGroup', 'asset_group_id', 'id')->withTrashed();
    }

    public function costCoa()
    {
        return $this->belongsTo('App\Models\Coa', 'cost_coa_id', 'id')->withTrashed();
    }

    public function depreciationDetail(){
        return $this->hasMany('App\Models\DepreciationDetail')->whereHas('depreciation',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function capitalizationDetail(){
        return $this->hasMany('App\Models\CapitalizationDetail')->whereHas('capitalization',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function retirementDetail(){
        return $this->hasMany('App\Models\RetirementDetail')->whereHas('retirement',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function balanceBookRaw(){
        $total = $this->nominal;

        foreach($this->depreciationDetail as $row){
            $total -= $row->nominal;
        }

        return $total;
    }

    public function qtyBalance(){
        $total = 0;

        foreach($this->capitalizationDetail as $row){
            $total += $row->qty;
        }

        foreach($this->retirementDetail as $row){
            $total -= $row->qty;
        }

        return $total;
    }

    public function nominalDepreciation(){
        $nominal = 0;

        if($this->method == '1'){
            $depreciation = round($this->nominal / $this->assetGroup->depreciation_period,3);
            $balance = $this->book_balance - $depreciation;
            if($balance > 0){
                $nominal = $depreciation;
            }else{
                $nominal = $this->book_balance;
            }
        }

        return $nominal;
    }

    public function checkDepreciationByMonth($month){
        $check = Depreciation::where('period',$month)->whereHas('depreciationDetail',function($query){
            $query->where('asset_id',$this->id);
        })->whereIn('status',['2','3'])->count();

        if($check > 0){
            return true;
        }else{
            return false;
        }
    }
    
    public function getUnitFromCapitalization()
    {
        $cek = CapitalizationDetail::where('asset_id',$this->id)->whereHas('capitalization')->first();

        if($cek){
            return $cek;
        }else{
            return '';
        }
    }
}
