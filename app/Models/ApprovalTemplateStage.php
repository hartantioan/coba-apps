<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateStage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_template_stages';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_template_id',
        'approval_stage_id',
    ];

    public function approvalTemplate(){
        return $this->belongsTo('App\Models\ApprovalTemplate', 'approval_template_id', 'id')->withTrashed();
    }

    public function approvalStage(){
        return $this->belongsTo('App\Models\ApprovalStage', 'approval_stage_id', 'id')->withTrashed();
    }
}
