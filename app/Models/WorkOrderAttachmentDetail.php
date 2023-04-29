<?php

namespace App\Models;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderAttachmentDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'work_order_attachment_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'work_order_id',
        'file_name',
        'path',
    ];

    public function workOrder(){
        return $this->belongsTo('App\Models\WorkOrder', 'work_order_id', 'id')->withTrashed();
    }

    public function attachment() 
    {
        if($this->path !== NULL && Storage::exists($this->path)) {
            $document_path = asset(Storage::url($this->path));
        } else {
            $document_path = asset('website/empty.png');
        }

        return $document_path;
    }

}
