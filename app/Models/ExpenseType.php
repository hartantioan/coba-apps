<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'expense_types';
    protected $fillable = [
        'name',
    ];

    public function expenseType()
    {
        return $this->hasMany('App\Models\ExpenseDetail');
    }
}
