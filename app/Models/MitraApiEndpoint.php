<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MitraApiEndpoint extends Model{
    use HasFactory, Notifiable;
    
    protected $table = 'mitra_api_endpoints';

    protected $primaryKey = 'id';
    protected $fillable   = [
        'mitra_id',             // id broker/mitra yang bersangkutan
        'base_url',             // base url dari broker
        'lookable_type',        // lookable_type di mitra_api_sync
        'operation',            // operation: index, store, show, update, delete. Disamakan dengan api mitra saja.
        'method',               // HTTP method: get, post, put, delete 
        'endpoint',             // endpoint dari operation
        'notes',                // catatan
    ];
}
