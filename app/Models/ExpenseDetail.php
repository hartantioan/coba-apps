<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseDetail extends Model
{
    use SoftDeletes;

    protected $table = 'expense_details';

    protected $fillable = [
        'expense_id',
        'expense_type_id',
        'note',
        'total',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }

}
