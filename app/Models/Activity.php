<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'activities';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'title',
        'description',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function workorder(){
        return $this->hasMany('App\Models\WorkOrder')->whereIn('status',['2','3']);
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public static function generateCode()
    {
        $query = Activity::selectRaw('RIGHT(code, 6) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return 'AC'.$no;
    }
}
