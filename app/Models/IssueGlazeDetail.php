<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class IssueGlazeDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'issue_glaze_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'issue_glaze_id',
        'lookable_type',
        'lookable_id',
        'note',
        'qty',
        'place_id',
        'warehouse_id',
        'total'
    ];

    public function issueGlaze(){
        return $this->belongsTo('App\Models\IssueGlaze', 'issue_glaze_id', 'id')->withTrashed();
    }
    public function lookable()
    {
        return $this->morphTo();
    }

    public function place()
    {
        return $this->belongsTo('App\Models\Place', 'place_id', 'id')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id', 'id')->withTrashed();
    }
}
