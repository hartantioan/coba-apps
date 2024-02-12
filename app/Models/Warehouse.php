<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'code',
        'name',
        'note',
        'is_transit_warehouse',
        'status'
    ];

    public static function generateCode()
    {
        $query = Warehouse::selectRaw('RIGHT(code, 3) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '001';
        }

        $no = str_pad($code, 3, 0, STR_PAD_LEFT);

        return 'WR'.$no;
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }
    
    public function isTransitWarehouse(){
        switch($this->is_transit_warehouse) {
            case '1':
                $transit = 'Ya';
                break;
            default:
                $transit = 'Tidak';
                break;
        }

        return $transit;
    }
}
