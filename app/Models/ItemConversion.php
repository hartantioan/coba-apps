<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemConversion extends Model
{
    protected $fillable = ['item_id', 'item_child_id','qty_conversion'];

    // Parent item
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    // Child item
    public function child(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_child_id');
    }
}
