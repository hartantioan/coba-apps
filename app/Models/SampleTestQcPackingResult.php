<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleTestQcPackingResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sample_test_qc_packing_results';

    protected $fillable = [
        'sample_test_input_id',
        'user_id',
        'dry_whiteness_value',
        'document',
        'note',
        'status',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Relationships
     */
    public function sampleTestInput()
    {
        return $this->belongsTo(SampleTestInput::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
