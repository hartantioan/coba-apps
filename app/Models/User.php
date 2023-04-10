<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'photo',
        'signature',
        'name',
        'employee_no',
        'password',
        'username',
        'phone',
        'address',
        'province_id',
        'city_id',
        'id_card',
        'id_card_address',
        'type',
        'group_id',
        'status',
        'company_id',
        'plant_id',
        'department_id',
        'position_id',
        'logo',
        'tax_id',
        'tax_name',
        'tax_address',
        'pic',
        'pic_no',
        'office_no',
        'email',
        'deposit',
        'limit_credit',
        'top',
        'top_internal',
        'gender',
        'married_status',
        'married_date',
        'children',
        'last_change_password',
        'country_id'
    ];

    protected $hidden = [
        'password',
    ];

    public function needChangePassword(){
        $days = now()->diffInDays(Carbon::parse($this->last_change_password));

        if($days >= 60){
            return true;
        }else{
            return false;
        }
    }

    public function type(){
        $type = match ($this->type) {
          '1' => 'Pegawai',
          '2' => 'Customer',
          '3' => 'Supplier',
          '4' => 'Expedisi',
          default => '',
        };

        return $type;
    }

    public function marriedStatus(){
        $marriedStatus = match ($this->married_status) {
          '1' => 'Single',
          '2' => 'Menikah',
          '3' => 'Cerai',
          default => 'Invalid',
        };

        return $marriedStatus;
    }

    public function gender(){
        $gender = match ($this->gender) {
          '1' => 'Pria',
          '2' => 'Wanita',
          '3' => 'Lainnya',
          default => 'Lainnya',
        };

        return $gender;
    }

    public function photo() 
    {
        if($this->photo !== NULL && Storage::exists($this->photo)) {
            $document = asset(Storage::url($this->photo));
        } else {
            $document = asset('website/empty_profile.png');
        }

        return $document;
    }

    public function profilePicture() 
    {
        return '<span class="avatar-status avatar-online" style="width:50px !important;"><img src="'.$this->photo().'" alt="avatar"></span>';
    }

    public function signature() 
    {
        return '<img src="'.asset(Storage::url($this->signature)).'" width="100px">';
    }

    public static function generateCode($type)
    {
        $prefix = '';

        if($type == '1'){
            $prefix = 'E';
        }elseif($type == '2'){
            $prefix = 'C';
        }elseif($type == '3'){
            $prefix = 'S';
        }elseif($type == '4'){
            $prefix = 'V';
        }

        $query = User::selectRaw('type, RIGHT(employee_no, 6) as code')
            ->where('type',$type)
            ->orderByDesc('id')
            ->limit(1)
            ->get();

        if($query->count() > 0) {
            $code = (int)$query[0]->code + 1;
        } else {
            $code = '000001';
        }

        $no = str_pad($code, 6, 0, STR_PAD_LEFT);

        return $prefix.$no;
    }

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

    public function userBank(){
        return $this->hasMany('App\Models\UserBank');
    }

    public function userData(){
        return $this->hasMany('App\Models\UserData');
    }

    public function defaultBank(){
        $bank = '';

        foreach(UserBank::where('user_id',$this->id)->where('is_default','1')->get() as $row){
            $bank = $row->name.' Rek. '.$row->no;
        }

        return $bank;
    }

    public function fundRequest(){
        return $this->hasMany('App\Models\FundRequest','account_id','id')->where('status','2');
    }

    public function purchaseDownPayment(){
        return $this->hasMany('App\Models\PurchaseDownPayment','account_id','id')->where('status','2');
    }

    public function purchaseInvoice(){
        return $this->hasMany('App\Models\PurchaseInvoice','account_id','id')->where('status','2');
    }

    public function userPlace(){
        return $this->hasMany('App\Models\UserPlace');
    }

    public function userPlaceArray(){
        $arr = [];
        foreach($this->userPlace as $row){
            $arr[] = $row->place_id;
        }
        return $arr;
    }

    public function userWarehouse(){
        return $this->hasMany('App\Models\UserWarehouse');
    }

    public function userWarehouseArray(){
        $arr = [];
        foreach($this->userWarehouse as $row){
            $arr[] = $row->warehouse_id;
        }
        return $arr;
    }

    public function userFile(){
        return $this->hasMany('App\Models\UserFile');
    }

    public function province(){
        return $this->belongsTo('App\Models\Region','province_id','id')->withTrashed();
    }

    public function group(){
        return $this->belongsTo('App\Models\Group','group_id','id')->withTrashed();
    }

    public function city(){
        return $this->belongsTo('App\Models\Region','city_id','id')->withTrashed();
    }

    public function country(){
        return $this->belongsTo('App\Models\Country','country_id','id')->withTrashed();
    }

    public function company(){
        return $this->belongsTo('App\Models\Company','company_id','id')->withTrashed();
    }

    public function place(){
        return $this->belongsTo('App\Models\Place','place_id','id')->withTrashed();
    }

    public function department(){
        return $this->belongsTo('App\Models\Department','department_id','id')->withTrashed();
    }

    public function position(){
        return $this->belongsTo('App\Models\Position','position_id','id')->withTrashed();
    }

    public function availablePurchaseOrder(){
        return $this->hasMany('App\Models\PurchaseOrder','account_id','id')->where('status','2');
    }

    public function checkMenu($id,$type){
        $cek = MenuUser::where('menu_id', $id)->where('user_id', $this->id)->where('type',$type)->first();

        if($cek){
            return true;
        }else{
            return false;
        }
    }

    public function checkPlace($id){
        $cek = UserPlace::where('place_id', $id)->where('user_id', $this->id)->first();

        if($cek){
            return 'checked';
        }else{
            return '';
        }
    }

    public function checkWarehouse($id){
        $cek = UserWarehouse::where('warehouse_id', $id)->where('user_id', $this->id)->first();

        if($cek){
            return 'checked';
        }else{
            return '';
        }
    }
}
