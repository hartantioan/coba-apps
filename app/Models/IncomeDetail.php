<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeDetail extends Model
{
    use SoftDeletes;

    protected $table = 'income_details';

    protected $fillable = [
        'income_id',
        'note',
        'total',
    ];
    public function income()
    {
        return $this->belongsTo(Income::class);
    }

}
