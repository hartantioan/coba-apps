<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintSalesDetail extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'complaint_sale_details';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'complaint_sales_id',
        'lookable_type',
        'lookable_id',
        'qty_color_mistake',
        'qty_motif_mistake',
        'qty_size_mistake',
        'qty_broken',
        'qty_mistake',
        'note',
    ];
    public function lookable(){
        return $this->morphTo();
    }
    public function complaintSale()
    {
        return $this->belongsTo(ComplaintSales::class, 'complaint_sales_id');
    }
}
