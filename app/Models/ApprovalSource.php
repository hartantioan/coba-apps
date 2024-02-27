<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalSource extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_sources';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'date_request',
        'lookable_type',
        'lookable_id',
        'note',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function fullUrl(){
        $menu = Menu::where('table_name',$this->lookable_type)->where('status','1')->first();

        if($menu){
            return $menu->fullUrl();
        }else{
            return '';
        }
    }

    public function fullName(){
        $menu = Menu::where('table_name',$this->lookable_type)->where('status','1')->first();

        if($menu){
            return $menu->fullName();
        }else{
            return '';
        }
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function approvalMatrix(){
        return $this->hasMany('App\Models\ApprovalMatrix');
    }

    public function getTemplateName(){
        $text = '';

        foreach($this->approvalMatrix as $row){
            $text = $row->approvalTemplateStage->approvalTemplate->code.' - '.$row->approvalTemplateStage->approvalTemplate->name;
        }

        return $text;
    }

    public function getAccountInfo(){
        $name = '';
        if(isset($this->lookable->account_id)){
            $name = $this->lookable->account->name;
        }else{
            if(isset($this->lookable->user_id)){
                $name = $this->lookable->user->name;
            }
        }
        return $name;
    }
}
