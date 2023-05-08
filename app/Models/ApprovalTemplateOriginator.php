<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateOriginator extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_template_originators';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_template_id',
        'user_id',
    ];

    public function approvalTemplate(){
        return $this->belongsTo('App\Models\ApprovalTemplate', 'approval_template_id', 'id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
}
