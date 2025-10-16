<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use SoftDeletes;

    protected $table = 'incomes';

    protected $fillable = [
        'user_id',
        'code',
        'post_date',
        'note',
        'grandtotal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = Income::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('code')
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

    public function incomeDetail()
    {
        return $this->hasMany(IncomeDetail::class);
    }

}
