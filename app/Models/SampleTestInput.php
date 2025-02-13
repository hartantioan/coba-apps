<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SampleTestInput extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sample_test_inputs';

    // If you want to protect certain columns from mass-assignment, specify them here
    protected $fillable = [
        'code',
        'user_id',
        'sample_type_id',
        'province_id',
        'city_id',
        'subdistrict_id',
        'village_name',
        'sample_date',
        'supplier',
        'supplier_name',
        'supplier_phone',
        'post_date',
        'link_map',
        'permission_type',
        'permission_name',
        'commodity_permits',
        'permits_period',
        'receiveable_capacity',
        'price_estimation',
        'supplier_sample_code',
        'company_sample_code',
        'document',
        'note',
        'lab_type',
        'lab_name',
        'wet_whiteness_value',
        'dry_whiteness_value',
        'document_test_result',
        'item_name',
        'test_result_note',
        'status',
    ];

    public function status(){
        switch($this->status) {
            case '1':
                $status = '<span class="gradient-45deg-green-teal medium-small white-text padding-3">Active</span>';
                break;
            case '2':
                $status = '<span class="gradient-45deg-red-pink medium-small white-text padding-3">Not Active</span>';
                break;
            default:
                $status = '<span class="gradient-45deg-amber-amber medium-small white-text padding-3">Invalid</span>';
                break;
        }

        return $status;
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function subdistrict(){
        return $this->belongsTo('App\Models\Region','subdistrict_id','id')->withTrashed();
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public static function generateCode($prefix)
    {
        $cek = substr($prefix,0,7);
        $query = SampleTestInput::selectRaw('RIGHT(code, 8) as code')
            ->whereRaw("code LIKE '$cek%'")
            ->withTrashed()
            ->orderByDesc('code')
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '00000001';
        }

        $no = str_pad($code, 8, 0, STR_PAD_LEFT);

        return substr($prefix,0,9).'-'.$no;
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->withTrashed();
    }

    public function attachment()
    {
        if($this->document !== NULL && Storage::exists($this->document)) {
            $document = asset(Storage::url($this->document));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function attachmentResult()
    {
        if($this->document_test_result !== NULL && Storage::exists($this->document_test_result)) {
            $document = asset(Storage::url($this->document_test_result));
        } else {
            $document = asset('website/empty.png');
        }

        return $document;
    }

    public function sampleType()
    {
        return $this->belongsTo(SampleType::class);
    }

    public function sampleTestInputPICNote(){
        return $this->hasOne('App\Models\SampleTestInputPICNote','sample_test_input_id','id');
    }

    public function labType(){
        $status = match ($this->lab_type) {
            '1' => 'Pabrik',
            '2' => 'Luar',
            default => '-',
        };

        return $status;
    }
}
