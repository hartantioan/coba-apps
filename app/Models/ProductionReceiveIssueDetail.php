<?php

namespace App\Models;

use App\Helpers\CustomHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionReceiveIssueDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_receive_issue_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_receive_issue_id',
        'production_batch_usage_id',
        'qty',
    ];

    public function productionReceiveIssue()
    {
        return $this->belongsTo('App\Models\ProductionReceiveIssue', 'production_receive_issue_id', 'id')->withTrashed();
    }

    public function productionBatchUsage()
    {
        return $this->belongsTo('App\Models\ProductionBatchUsage', 'production_batch_usage_id', 'id')->withTrashed();
    }
}
