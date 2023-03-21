<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'journals';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'account_id',
        'code',
        'lookable_type',
        'lookable_id',
        'post_date',
        'due_date',
        'note'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function lookable(){
        return $this->morphTo();
    }

    public function journalDetail()
    {
        return $this->hasMany('App\Models\JournalDetail');
    }

    public static function generateCode()
    {
        $query = Journal::selectRaw('RIGHT(code, 11) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000000001';
        }

        $no = str_pad($code, 11, 0, STR_PAD_LEFT);

        $pre = 'JR-'.date('y').date('m').date('d').'-';

        return $pre.$no;
    }
}
