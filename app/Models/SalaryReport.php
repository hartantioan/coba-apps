<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class SalaryReport extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $table = 'salary_reports';
    protected $fillable = [
        'code',
        'period_id',
        'post_date'
    ];

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = SalaryReport::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }
}
