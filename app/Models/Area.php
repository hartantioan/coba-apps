<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'areas';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'name',
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
    

    public static function generateCode()
    {
        $query = Area::selectRaw('RIGHT(code, 6) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return 'AR'.$no;
    }

    public function workorder(){
        return $this->hasMany('App\Models\WorkOrder')->whereIn('status',['2','3']);
    }
}
