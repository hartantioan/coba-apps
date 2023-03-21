<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'allowances';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'name',
        'type'
    ];

    public function type(){
        switch($this->type) {
            case '1':
                $type = 'Penambah';
                break;
            case '2':
                $type = 'Pengurang';
                break;
            default:
                $type = 'Invalid';
                break;
        }

        return $type;
    }
}
