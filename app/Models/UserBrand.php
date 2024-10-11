<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserBrand extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_brands'; // Specify the table name if different
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'account_id',
        'brand_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    public function account()
    {
       return $this->belongsTo('App\Models\User','account_id','id');
    }

    // Example of a relationship to Brand
    public function brand()
    {
        return $this->belongsTo('App\Models\Brand','brand_id','id');
    }
}
