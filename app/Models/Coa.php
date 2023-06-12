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
        'prefix',
        'name',
        'company_id',
        'parent_id',
        'level',
        'status',
        'is_confidential',
        'is_control_account',
        'is_cash_account',
        'is_hidden',
        'show_journal',
        'bp_journal',
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
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
