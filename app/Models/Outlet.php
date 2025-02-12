<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'outlets';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'code',
        'name',
        'type',
        'outlet_group_id',
        'group_bp_id',
        'address',
        'phone',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'link_gmap',
        'status',
        'location_type',//grouping
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function outletGroup(){
        return $this->belongsTo('App\Models\GroupOutlet','outlet_group_id','id')->withTrashed();
    }

    public function groupBP(){
        return $this->belongsTo('App\Models\Group','group_bp_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function district(){
        return $this->belongsTo('App\Models\Region','district_id','id')->withTrashed();
    }

    public function subdistrict(){
        return $this->belongsTo('App\Models\Region','subdistrict_id','id')->withTrashed();
    }

    public static function generateCode()
    {
        $query = Outlet::selectRaw('LEFT(code, 5) as prefix')
        ->orderByDesc('code')
        ->limit(1)
        ->first();

        $prefix = $query ? $query->prefix : '00000';

        $latestCodeQuery = Outlet::selectRaw('RIGHT(code, 5) as code')
            ->whereRaw("code LIKE '$prefix%'")
            ->withTrashed()
            ->orderByDesc('code')
            ->limit(1)
            ->get();

        if ($latestCodeQuery->count() > 0) {
            $code = (int)$latestCodeQuery[0]->code + 1;
        } else {
            $code = 1;
        }

        $no = str_pad($code, 5, '0', STR_PAD_LEFT);

        return $no;
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusRaw(){
        $status = match ($this->status) {
          '1' => 'Aktif',
          '2' => 'Tidak Aktif',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function locationType(){
        $status = match ($this->location_type) {
          '1' => 'Toko',
          '2' => 'Gudang',
          default => '-',
        };

        return $status;
    }

    public function type(){
        $type = match ($this->type) {
          '1'   => 'Supermarket',
          '2'   => 'Hypermarket',
          '3'   => 'Minimarket',
          '4'   => 'Koperasi',
          '5'   => 'Toko Online',
          '6'   => 'Marketplace',
          '7'   => 'Institusi',
          '8'   => 'Toko Offline',
          default => 'Invalid',
        };

        return $type;
    }
}
