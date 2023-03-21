<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'coas';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'company_id',
        'parent_id',
        'level',
        'type',
        'status',
        'is_confidential',
        'is_control_account',
        'is_cash_account',
    ];

    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }

    public function parentSub(){
        return $this->belongsTo('App\Models\Coa', 'parent_id', 'id')->withTrashed();
    }

    public function childSub(){
        return $this->hasMany('App\Models\Coa', 'parent_id', 'id');
    }

    public function child(){
        $query = Coa::where('parent_id',$this->id)->orderBy('code')->get();
        return $query;
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }
}
