<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class UserFamily extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'user_families';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'user_id',
        'code',
        'relation',
        'emergency_contact',
        'address',
        'id_number',
        'marriage_status',
        'religion',
        'job',
        'birth_date',
    ];

    public static function generateCode()
    {
        $query = UserFamily::selectRaw('RIGHT(code, 6) as code')
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

        return 'UF'.$no;
    }

    public function religion(){
        $religion = match ($this->religion) {
            '1' => 'Kristen',
            '2' => 'Islam',
            '3' => 'Katolik',
            '4' => 'Buddha',
            '5' => 'Kong Hu Cu',
            '6' => 'Hindu',
            '7' => 'Others',
            default => 'Invalid',
        };

        return $religion;
    }

    public function relation(){
        $relation = match ($this->relation) {
            '1' => 'Suami',
            '2' => 'Istri',
            '3' => 'Anak',
            '4' => 'Kakek',
            '5' => 'Nenek',
            '6' => 'Cucu',
            '7' => 'Sepupu',
            '8' => 'Paman',
            '9' => 'Bibi',
            '10' => 'Others',
            '11' => 'Adik',
            '12' => 'Kakak',
            default => 'Invalid',
        };

        return $relation;
    }

    public function marriageStatus(){
        $marriageStatus = match ($this->marriage_status) {
            '1' => 'Single',
            '2' => 'Married',
            '3' => 'Widow',
            '4' => 'Widower',
            '5' => 'Nenek',
            '6' => 'Cucu',
            '7' => 'Sepupu',
            '8' => 'Paman',
            '9' => 'Bibi',
            '10' => 'Others',
            '11' => 'Adik',
            '12' => 'Kakak',
            default => 'Invalid',
        };

        return $marriageStatus;
    }
}
