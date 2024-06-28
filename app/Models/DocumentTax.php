<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DocumentTax extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $table = 'document_taxes';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'transaction_code',
        'replace',
        'code',
        'date',
        'npwp_number',
        'npwp_name',
        'npwp_address',
        'npwp_target',
        'npwp_target_name',
        'npwp_target_address',
        'total',
        'tax',
        'wtax',
        'approval_status',
        'tax_status',
        'reference',
        'url',
        'user_id',
        'status',
    ];

    public function documentTaxDetail()
    {
        return $this->hasMany('App\Models\DocumentTaxDetail');
    }

    public function documentTaxHandoverDetail()
    {
        return $this->hasOne('App\Models\DocumentTaxHandoverDetail')->whereIn('status',['1','2']);
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function status(){
        $status = match ($this->status) {
          '1' => 'Pending',
          '2' => 'Digunakan',
          '3' => 'Ditolak',
          '4' => 'Disetujui',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }

    public function reverseStatus(){
        $status = match ($this->status) {
          'Pending' => '1',
          'Digunakan' => '2',
          'Ditolak' => '3',
          'Disetujui' => '4',
          default => '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>',
        };

        return $status;
    }


}
