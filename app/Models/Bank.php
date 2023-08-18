<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'banks';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'account_name',
        'account_no',
        'company_id',
        'branch',
        'is_show',
        'status',
    ];

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function isShow(){
        $show = match ($this->is_show) {
          '1' => 'Ya',
          default => 'Tidak',
        };

        return $show;
    }

    public function company(){
        return $this->belongsTo('App\Models\Company', 'company_id', 'id')->withTrashed();
    }
}