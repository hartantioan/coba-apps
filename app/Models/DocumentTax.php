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
        'url'
    ];

    public function documentTaxDetail()
    {
        return $this->hasMany('App\Models\DocumentTaxDetail');
    }
}
