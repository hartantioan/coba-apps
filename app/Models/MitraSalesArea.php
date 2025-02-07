<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraSalesArea extends Model{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'mitra_sales_areas';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    public    $model_name = 'Sales Area Mitra';
    protected $fillable   = [
        'code',         // code & nama, saat ini isinya disamakan
        'name',
        'type',         // diisi Kota/Kabupaten
        'mitra_id',     // ID Broker (employee_no) dari table user
        'status',
    ];

    public function mitra(){
        return $this->belongsTo('App\Models\User', 'mitra_id', 'id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function mitraApiSyncDatas(){
        return $this->morphMany(MitraApiSyncData::class, 'lookable');
    }
}
