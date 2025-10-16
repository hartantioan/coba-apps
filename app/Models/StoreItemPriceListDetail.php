<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreItemPriceListDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'store_item_price_list_details';

    protected $fillable = [
        'store_item_price_list_id',
        'selling_category_id',
        'price',
        'discount',
    ];

    // Optionally, define relationships here if needed, for example:
    public function storeItemPriceList()
    {
        return $this->belongsTo(StoreItemPriceList::class);
    }

    public function sellingCategory()
    {
        return $this->belongsTo(SellingCategory::class);
    }

    // You can also define casts if you want auto conversion

}
