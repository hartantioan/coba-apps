<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionReceiveIssue extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_receive_issues';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_receive_id',
        'production_issue_id',
        'is_auto',
    ];

    public function productionReceive()
    {
        return $this->belongsTo('App\Models\ProductionReceive', 'production_receive_id', 'id')->withTrashed();
    }

    public function productionIssue()
    {
        return $this->belongsTo('App\Models\ProductionIssue', 'production_issue_id', 'id')->withTrashed();
    }

    public function productionReceiveIssueDetail()
    {
        return $this->hasMany('App\Models\ProductionReceiveIssueDetail');
    }

    public function listBatchUsed(){
        if($this->productionReceiveIssueDetail()->exists()){
            $arr = '<ol>';
            foreach($this->productionReceiveIssueDetail as $row){
                $arr .= '<li>'.$row->productionBatchUsage->productionBatch->code.' Qty Terpakai : '.CustomHelper::formatConditionalQty($row->qty).'</li>';
            }
            $arr .= '</ol>';
        }else{
            $arr = '';
        }
        
        return $arr;
    }
}
