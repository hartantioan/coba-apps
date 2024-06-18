<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalStage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_stages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'approval_id',
        'level',
        'min_approve',
        'min_reject',
        'status',
    ];

    public function approval(){
        return $this->belongsTo('App\Models\Approval', 'approval_id', 'id')->withTrashed();
    }

    public function approvalStageDetail()
    {
        return $this->hasMany('App\Models\ApprovalStageDetail');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
    public function statusRaw(){
        $status = match ($this->status) {
          '1' => 'Aktif',
          '2' => 'Tidak Aktif',
          default => '-',
        };

        return $status;
    }

    public function listApprover(){
        $list = '<ol>';
        
        foreach($this->approvalStageDetail as $row){
            $list .= '<li class="left-align">'.$row->user->name.'</li>';
        }

        $list .= '<ol>';

        return $list;
    }

    public function textApprover(){
        $list = [];
        
        foreach($this->approvalStageDetail as $row){
            $list[] = $row->user->name;
        }

        return implode(', ',$list);
    }
}
