<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SampleTestInputPICNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sample_test_input_pic_notes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'sample_test_input_id',
        'status',
        'note',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sampleTestInput()
    {
        return $this->belongsTo(SampleTestInput::class, 'id');
    }

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function statusRaw(){
        switch($this->status) {
            case '1':
                $status = 'Active';
                break;
            case '2':
                $status = 'Not Active';
                break;
            default:
                $status = 'Invalid';
                break;
        }

        return $status;
    }
}
