<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MitraPriceList extends Model{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table      = 'mitra_price_lists';
    protected $primaryKey = 'id';
    protected $dates      = ['deleted_at'];
    public    $model_name = "Price List Mitra";
    protected $fillable   = [
        'sales_area_code',  // sales_area
        'variety_id',       // Variety/Group HT
        'type_id',          // Type/Jenis Plain
        'package_id',       // Palet / Box, dari tabel pallet, untuk lookup harga saja
        'effective_date',
        'uom_id',           // UOM minimal qty, misal 2 pallet maka diisi pallet (bukan uom barang)
        'min_qty',          // Minimal QTY dalam satuan palet
        'price_exclude',    // Harga dalam M2
        'price_include',    // Harga dalam M2
        'mitra_id',         //ID Broker (employee_no) dari table user
        'price_group_code', //untuk tirta: RTL
        'status',
    ];

    //mengisi default value
    protected $attributes = [
        'price_group_code' => 'RTL',     //default value dari Tirta => 'RTL'
    ];

    public function variety(){
        return $this->belongsTo('App\Models\Variety', 'variety_id', 'id')->withTrashed();
    }

    public function type(){
        return $this->belongsTo('App\Models\Type', 'type_id', 'id')->withTrashed();
    }

    public function package(){
        return $this->belongsTo('App\Models\Pallet', 'package_id', 'id')->withTrashed();
    }

    public function uom(){
        return $this->belongsTo('App\Models\Unit', 'uom_id', 'id')->withTrashed();
    }

    public function mitra(){
        return $this->belongsTo('App\Models\User', 'mitra_id', 'id')->withTrashed();
    }

    public function getCodeAttribute(){
        return $this->attributes['sales_area_code'];
    }

    public function getNameAttribute(){
        return $this->variety->name."-".$this->type->name;
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
