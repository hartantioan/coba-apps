<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ReceiveGlazeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'receive_glaze_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'receive_glaze_id', 
        'issue_glaze_id', 
        'qty',
        'total',
    ];

    public function receiveGlaze()
    {
        return $this->belongsTo(ReceiveGlaze::class);
    }

    public function issueGlaze()
    {
        return $this->belongsTo(IssueGlaze::class);
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
