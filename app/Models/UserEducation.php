<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserEducation extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_educations';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'stage',
        'code',
        'school_name',
        'major',
        'final_score',
        'year_start',
        'year_end',
    ];

    public static function generateCode()
    {
        $query = UserEducation::selectRaw('RIGHT(code, 6) as code')
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

        return 'UE'.$no;
    }
    public function stage(){
        $stage = match ($this->stage) {
            '1' => 'SD',
            '2' => 'SMP',
            '3' => 'SMA',
            '4' => 'D1',
            '5' => 'D2',
            '6' => 'D3',
            '7' => 'D4',
            '8' => 'S1',
            '9' => 'S2',
            '10' => 'S3',
            default => 'Invalid',
        };

        return $stage;
    }
}
