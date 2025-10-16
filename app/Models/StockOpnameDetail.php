<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameDetail extends Model
{
    use SoftDeletes;

    protected $table = 'stock_opname_details';

    protected $fillable = [
        'stock_opname_id',
        'item_id',
        'type',
        'adjustment_qty',
        'actual_qty',
        'note',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
