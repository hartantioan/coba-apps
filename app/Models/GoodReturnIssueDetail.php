<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GoodReturnIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_return_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_return_issue_id',
        'good_issue_detail_id',
        'item_id',
        'qty',
        'note',
        'total',
    ];

    public function goodReturnIssue()
    {
        return $this->belongsTo('App\Models\GoodReturnIssue', 'good_return_issue_id', 'id')->withTrashed();
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->getTable())->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }

    public function goodIssueDetail()
    {
        return $this->belongsTo('App\Models\GoodIssueDetail', 'good_issue_detail_id', 'id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo('App\Models\Item', 'item_id', 'id');
    }
}
