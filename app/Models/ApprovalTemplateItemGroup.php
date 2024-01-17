<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateItemGroup extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_template_item_groups';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_template_id',
        'item_group_id',
    ];

    public function approvalTemplate(){
        return $this->belongsTo('App\Models\ApprovalTemplate', 'approval_template_id', 'id')->withTrashed();
    }

    public function itemGroup(){
        return $this->belongsTo('App\Models\ItemGroup', 'item_group_id', 'id')->withTrashed();
    }
}
