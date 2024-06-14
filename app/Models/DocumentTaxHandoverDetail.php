<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTaxHandoverDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'document_tax_handover_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'document_tax_id',
        'document_tax_handover_id',
        'status',
    ];

    public function documentTax(){
        return $this->belongsTo('App\Models\DocumentTax','document_tax_id','id');
    }

    public function documentTaxHandover(){
        return $this->belongsTo('App\Models\documentTaxHandover','document_tax_handover_id','id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => 'Pending',
          '2' => 'Approved',
          '3' => 'Ditolak',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function statusColor(){
        $status = match ($this->status) {
          '1' => 'yellow',
          '2' => 'green',
          '3' => 'red',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }
}
