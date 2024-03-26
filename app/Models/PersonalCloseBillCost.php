<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PersonalCloseBillCost extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'personal_close_bill_costs';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'personal_close_bill_id',
        'note',
        'qty',
        'unit_id',
        'price',
        'tax_id',
        'percent_tax',
        'is_include_tax',
        'wtax_id',
        'percent_wtax',
        'total',
        'tax',
        'wtax',
        'grandtotal',
        'place_id',
        'line_id',
        'machine_id',
        'division_id',
        'project_id',
    ];

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function line(){
        return $this->belongsTo('App\Models\Line','line_id','id')->withTrashed();
    }

    public function machine(){
        return $this->belongsTo('App\Models\Machine','machine_id','id')->withTrashed();
    }

    public function division(){
        return $this->belongsTo('App\Models\Division','division_id','id')->withTrashed();
    }

    public function project(){
        return $this->belongsTo('App\Models\Project','project_id','id')->withTrashed();
    }

    public function isIncludeTax(){
        $type = match ($this->is_include_tax) {
          '0' => 'Tidak',
          '1' => 'Termasuk',
          default => 'Invalid',
        };

        return $type;
    }

    public function taxMaster(){
        return $this->belongsTo('App\Models\Tax','tax_id','id')->withTrashed();
    }

    public function wtaxMaster(){
        return $this->belongsTo('App\Models\Tax','wtax_id','id')->withTrashed();
    }

    public function personalCloseBill()
    {
        return $this->belongsTo('App\Models\PersonalCloseBill', 'personal_close_bill_id', 'id')->withTrashed();
    }

    public function unit()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id', 'id')->withTrashed();
    }
}
