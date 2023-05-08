<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTemplateMenu extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_template_menus';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'approval_template_id',
        'menu_id',
        'table_name',
    ];

    public function approvalTemplate(){
        return $this->belongsTo('App\Models\ApprovalTemplate', 'approval_template_id', 'id')->withTrashed();
    }

    public function menu(){
        return $this->belongsTo('App\Models\Menu', 'menu_id', 'id')->withTrashed();
    }
}
