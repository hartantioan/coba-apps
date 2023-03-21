<?php

namespace App\Models;

use App\Helpers\CustomHelper;
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
        'approval_table_id',
        'approval_source_id',
        'note',
        'user_id',
        'date_request',
        'date_process',
        'approved',
        'rejected',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function approvalTable(){
        return $this->belongsTo('App\Models\ApprovalTable', 'approval_table_id', 'id')->withTrashed();
    }

    public function approvalSource(){
        return $this->belongsTo('App\Models\ApprovalSource', 'approval_source_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '0' => 'Menunggu',
          '1' => 'Dibuka',
          '2' => 'Ditutup',
          default => 'Invalid',
        };

        return $status;
    }

    public function isTopLevel(){
        $max = ApprovalTable::where('menu_id',$this->approvalTable->menu_id)->where('status','1')->max('level');

        if($this->approvalTable->level == $max){
            return true;
        }else{
            return false;
        }
    }

    public function updateNextLevelApproval(){
        $approvalTable = ApprovalTable::where('table_name',$this->approvalSource->lookable_type)->where('status','1')->orderBy('level')->get();

        $arrTemp = [];
        
        foreach($approvalTable as $row){
            $cek = null;
            $cek = ApprovalMatrix::where('approval_table_id',$row->id)->where('approval_source_id',$this->approval_source_id)->count();

            if($cek > 0){
                $countApproved = ApprovalMatrix::where('approval_table_id',$row->id)->where('approval_source_id',$this->approval_source_id)->where('status','2')->whereNotNull('approved')->count();

                if($row->min_approve == $countApproved){
                    
                }else{
                    $arrTemp[] = [
                        'level' => $row->level,
                        'id'    => $row->id,
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

        $cek = ApprovalMatrix::where('approval_source_id',$this->approval_source_id)->where('approval_table_id',$this->approval_table_id)->where('status','2')->whereNotNull('approved')->count();

        if($this->approvalTable->min_approve == $cek){
            return true;
        }else{
            return false;
        }
    }

    public function passedReject(){

        $cek = ApprovalMatrix::where('approval_source_id',$this->approval_source_id)->where('approval_table_id',$this->approval_table_id)->where('status','2')->whereNotNull('rejected')->count();

        if($this->approvalTable->min_reject == $cek){
            return true;
        }else{
            return false;
        }
    }
}
