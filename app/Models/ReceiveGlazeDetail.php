<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiveGlazeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'receive_glaze_id', 'issue_glaze_id', 'qty'
    ];

    public function receiveGlaze()
    {
        return $this->belongsTo(ReceiveGlaze::class);
    }

    public function issueGlaze()
    {
        return $this->belongsTo(IssueGlaze::class);
    }
}
