<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SampleTestResult extends Model
{

    use HasFactory, SoftDeletes;

    protected $table = 'sample_test_results'; // Explicitly defining the table name

    protected $fillable = [
        'sample_test_input_id',
        'user_id',
        'lab_name',
        'wet_whiteness_value',
        'dry_whiteness_value',
        'document',
        'item_name',
        'note',
        'status',
    ];

    public function sampleTestInput()
    {
        return $this->belongsTo(SampleTestInput::class, 'sample_test_input_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachment()
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }
}
