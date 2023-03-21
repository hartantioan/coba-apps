<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTableDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_table_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_table_id',
        'user_id'
    ];

    public function approvalTable(){
        return $this->belongsTo('App\Models\ApprovalTable', 'approval_table_id', 'id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }
}
