<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ResourceGroup extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'item_groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'other_name',
        'parent_id',
        'coa_id',
        'status'
    ];


    public function parentSub(){
        return $this->belongsTo('App\Models\ResourceGroup', 'parent_id', 'id')->withTrashed();
    }

    public function childSub(){
        return $this->hasMany('App\Models\ResourceGroup', 'parent_id', 'id');
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
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
