<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalMatrix extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'approval_matrixs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'approval_template_stage_id',
        'approval_source_id',
        'note',
        'user_id',
        'date_request',
        'date_process',
        'approved',
        'rejected',
        'revised',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function approvalTemplateStage(){
        return $this->belongsTo('App\Models\ApprovalTemplateStage', 'approval_template_stage_id', 'id')->withTrashed();
    }

    public function approvalSource(){
        return $this->belongsTo('App\Models\ApprovalSource')->withTrashed();
    }

    public function statusApproval(){
        $status = '';
        if($this->approved){
            $status = 'setujui';
        }elseif($this->rejected){
            $status = 'tolak';
        }elseif($this->revised){
            $status = 'revisi';
        }
        return $status;
    }

    public function status(){
        $status = match ($this->status) {
          '0' => 'Menunggu',
          '1' => 'Dibuka',
          '2' => 'Ditutup',
          '3' => 'Revisi',
          default => 'Invalid',
        };

        return $status;
    }

    public function checkOtherApproval(){
        $otherSource = ApprovalSource::where('lookable_type',$this->approvalSource->lookable_type)->where('lookable_id',$this->approvalSource->lookable_id)->where('id','!=',$this->approval_source_id)->get();

        $passed = true;

        foreach($otherSource as $source){
            foreach($source->approvalMatrix as $row){
                $countApproved = $source->approvalMatrix()->where('approval_template_stage_id',$row->approval_template_stage_id)->whereNotNull('approved')->where('status','2')->count();
                if($countApproved < $row->approvalTemplateStage->approvalStage->min_approve){
                    $passed = false;
                }
            }
        }

        return $passed;
    }

    public function updateNextLevelApproval(){

        $arrTemp = [];

        $listTemplateStage = [];
        
        foreach($this->approvalTemplateStage->approvalTemplate->approvalTemplateStage()->withTrashed()->orderBy('id')->get() as $row){
            $listTemplateStage[] = [
                'approval_template_stage_id'    => $row->id,
                'min_approve'                   => $row->approvalStage->min_approve,
            ];
        }

        foreach($listTemplateStage as $rowstage){
            $cek = null;
            $cek = ApprovalMatrix::where('approval_template_stage_id',$rowstage['approval_template_stage_id'])->where('approval_source_id',$this->approval_source_id)->count();

            if($cek > 0){
                $countApproved = ApprovalMatrix::where('approval_template_stage_id',$rowstage['approval_template_stage_id'])->where('approval_source_id',$this->approval_source_id)->where('status','2')->whereNotNull('approved')->count();

                if($rowstage['min_approve'] <= $countApproved){
                    
                }else{
                    $arrTemp[] = [
                        'id'    => $rowstage['approval_template_stage_id'],
                    ];
                }
            }
        }

        if(count($arrTemp) > 0){
            return $arrTemp[0]['id'];
        }else{
            return '';
        }
    }

    public function passedApprove(){

        $cek = ApprovalMatrix::where('approval_source_id',$this->approval_source_id)->where('approval_template_stage_id',$this->approval_template_stage_id)->where('status','2')->whereNotNull('approved')->count();

        if($this->approvalTemplateStage->approvalStage->min_approve == $cek){
            return true;
        }else{
            return false;
        }
    }

    public function passedReject(){

        $cek = ApprovalMatrix::where('approval_source_id',$this->approval_source_id)->where('approval_template_stage_id',$this->approval_template_stage_id)->where('status','2')->whereNotNull('rejected')->count();

        if($this->approvalTemplateStage->approvalStage->min_reject == $cek){
            return true;
        }else{
            return false;
        }
    }
}
