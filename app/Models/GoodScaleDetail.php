<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Monolog\Formatter\FormatterInterface;

class GoodScaleDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'good_scale_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'good_scale_id',
        'lookable_type',
        'lookable_id',
        'qty',
        'total'
    ];

    public function lookable(){
        return $this->morphTo();
    }

    public function goodScale()
    {
        return $this->belongsTo('App\Models\GoodScale', 'good_scale_id', 'id');
    }

    public function journalDetail(){
        return $this->hasMany('App\Models\JournalDetail','detailable_id','id')->where('detailable_type',$this->table)->whereHas('journal',function($query){
            $query->whereIn('status',['2','3']);
        });
    }
}
