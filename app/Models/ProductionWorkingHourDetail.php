<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductionWorkingHourDetail extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'production_working_hour_details';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'production_working_hour_id',
        'type',
        'note',
        'working_hour',
    ];

    public function type(){
        info($this->type);
        $type = match ($this->type) {
            '1' => 'Production',
            '2' => 'Non-Production',

            default => 'Invalid',
        };
        info($type);
        return $type;
    }
}
