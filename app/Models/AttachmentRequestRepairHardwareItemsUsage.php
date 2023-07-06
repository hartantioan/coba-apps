<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class AttachmentRequestRepairHardwareItemsUsage extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'attachment_request_repair_hardware_item_usage_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [    
        'request_repair_hardware_items_usage_id',
        'file_name',
        'path',
    ];

    public function requestRepairHardware(){
        return $this->belongsTo('App\Models\RequestRepairHardwareItemsUsage', 'request_repair_hardware_items_usage_id', 'id')->withTrashed();
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
