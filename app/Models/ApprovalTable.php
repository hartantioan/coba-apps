<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTable extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_tables';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'approval_id',
        'menu_id',
        'table_name',
        'level',
        'is_check_nominal',
        'sign',
        'nominal',
        'status',
        'min_approve',
        'min_reject'
    ];

    public function approval(){
        return $this->belongsTo('App\Models\Approval', 'approval_id', 'id')->withTrashed();
    }

    public function menu(){
        return $this->belongsTo('App\Models\Menu', 'menu_id', 'id')->withTrashed();
    }

    public function approvalTableDetail()
    {
        return $this->hasMany('App\Models\ApprovalTableDetail');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function approvalMatrix()
    {
        return $this->hasMany('App\Models\ApprovalMatrix');
    }
}
