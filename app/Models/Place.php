<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'places';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'address',
        'company_id',
        'type',
        'province_id',
        'city_id',
        'status'
    ];

    public static function generateCode()
    {
        $query = Place::selectRaw('RIGHT(code, 3) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '001';
        }

        $no = str_pad($code, 3, 0, STR_PAD_LEFT);

        return 'PL'.$no;
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id')->withTrashed();
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'Kantor',
          '2' => 'Pabrik',
          default => 'Invalid',
        };

        return $type;
    }
}
