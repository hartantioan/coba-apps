<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JournalDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'journal_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'journal_id',
        'cost_distribution_detail_id',
        'coa_id',
        'place_id',
        'line_id',
        'machine_id',
        'account_id',
        'department_id',
        'warehouse_id',
        'type',
        'nominal',
        'note',
    ];

    public function journal(){
        return $this->belongsTo('App\Models\Journal', 'journal_id', 'id')->withTrashed();
    }

    public function costDistributionDetail(){
        return $this->belongsTo('App\Models\CostDistributionDetail', 'cost_distribution_detail_id', 'id')->withTrashed();
    }

    public function coa(){
        return $this->belongsTo('App\Models\Coa', 'coa_id', 'id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line', 'line_id', 'id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine', 'machine_id', 'id')->withTrashed();
    }

    public function warehouse(){
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }

    public function account(){
        return $this->belongsTo('App\Models\User', 'account_id', 'id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function department(){
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }

    public function type(){
        $type = match ($this->type) {
          '1' => '<span class="green medium-small white-text padding-3">Debit</span>',
          '2' => '<span class="red medium-small white-text padding-3">Kredit</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $type;
    }
    
}
