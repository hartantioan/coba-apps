<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use SoftDeletes;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'code',
        'post_date',
        'user_id',
        'note',
    ];

    protected $dates = [
        'post_date',
        'deleted_at',
    ];

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = StockOpname::selectRaw('RIGHT(code, 8) as code')
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
