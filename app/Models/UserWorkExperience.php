<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserWorkExperience extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_work_experiences';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'code',
        'month_start',
        'month_end',
        'position',
        'company_name',
        'job_description',
    ];

    public static function generateCode()
    {
        $query = UserWorkExperience::selectRaw('RIGHT(code, 6) as code')
            ->withTrashed()
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return 'UWE'.$no;
    }
}
