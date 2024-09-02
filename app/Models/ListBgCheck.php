<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ListBgCheck extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'list_bg_checks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'company_id',
        'post_date',
        'valid_until_date',
        'pay_date',
        'bank_source_name',
        'bank_source_no',
        'document_no',
        'document',
        'note',
        'nominal',
        'grandtotal',
        'status',
    ];
    
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function account()
    {
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
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

    public function statusRaw(){
        $status = match ($this->status) {
            '1' => 'Active',
            '2' => 'Non-Active',
            default => 'Invalid',
        };

        return $status;
    }
}
