<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalStageDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_stage_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_stage_id',
        'user_id'
    ];

    public function approvalStage(){
        return $this->belongsTo('App\Models\ApprovalStage', 'approval_stage_id', 'id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
}
