<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonStaffCompany extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'non_staff_companies';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code',
        'user_id',
        'account_id',
        'vendor_id',
        'post_date',
        'document_no',
        'note',
        'status',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User','account_id','id')->withTrashed();
    }

    public function vendor(){
        return $this->belongsTo('App\Models\User','vendor_id','id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
