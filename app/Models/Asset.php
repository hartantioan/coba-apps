<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'assets';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'user_id',
        'place_id',
        'name',
        'asset_group_id',
        'date',
        'nominal',
        'method',
        'cost_coa_id',
        'note',
        'status'
    ];

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function method(){
        $method = match ($this->method) {
          '1' => 'Straight Line',
          '2' => 'Declining Balance',
          default => 'Invalid',
        };

        return $method;
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }
    public function assetGroup()
    {
        return $this->belongsTo('App\Models\AssetGroup', 'asset_group_id', 'id')->withTrashed();
    }

    public function costCoa()
    {
        return $this->belongsTo('App\Models\Coa', 'cost_coa_id', 'id')->withTrashed();
    }
}
