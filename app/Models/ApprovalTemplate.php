<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprovalTemplate extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_templates';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'name',
        'is_check_nominal',
        'is_check_benchmark',
        'is_coa_detail',
        'nominal_type',
        'sign',
        'nominal',
        'nominal_final',
        'min_approve',
        'min_reject',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function approvalTemplateMenu()
    {
        return $this->hasMany('App\Models\ApprovalTemplateMenu');
    }

    public function approvalTemplateItemGroup()
    {
        return $this->hasMany('App\Models\ApprovalTemplateItemGroup');
    }

    public function itemGroupList(){
        $arr = [];
        foreach($this->approvalTemplateItemGroup as $row){
            $arr[] = $row->itemGroup->name;
        }
        return implode(',',$arr);
    }
    
    public function approvalTemplateOriginator()
    {
        return $this->hasMany('App\Models\ApprovalTemplateOriginator');
    }

    public function approvalTemplateStage()
    {
        return $this->hasMany('App\Models\ApprovalTemplateStage');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function nominalType(){
        $status = match ($this->nominal_type) {
          '1' => 'Grandtotal (Rp)',
          '2' => 'Prosentase (%)',
          default => 'Kosong',
        };

        return $status;
    }

    public function sign(){
        $status = match ($this->sign) {
            '>'     => 'lebih dari',
            '>='    => 'lebih dari sama dengan',
            '='     => 'sama dengan',
            '<'     => 'kurang dari',
            '<='    => 'kurang dari sama dengan',
            '~'     => 'dalam range',
            default => 'Kosong',
        };

        return $status;
    }

    public function formatSignNominal(){
        $text = '';
        if($this->sign == '~'){
            $text = number_format($this->nominal,2,',','.').' '.$this->sign.' '.number_format($this->nominal_final,2,',','.');
        }else{
            $text = $this->sign.' '.number_format($this->nominal,2,',','.');
        }
        return $text;
    }
}
