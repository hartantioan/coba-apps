<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'companies';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'address',
        'province_id',
        'city_id',
        'npwp_no',
        'npwp_name',
        'npwp_address',
        'status',
    ];

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id');
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>',
          '2' => '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };
        return $status;
    }

    public static function generateCode()
    {
        $query = Company::selectRaw('RIGHT(code, 3) as code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '001';
        }

        $no = str_pad($code, 3, 0, STR_PAD_LEFT);

        return 'BR'.$no;
    }
}
