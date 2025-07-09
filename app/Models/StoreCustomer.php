<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class StoreCustomer extends Model
{
    use HasFactory;

    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'store_customers';

    protected $fillable = [
        'code',
        'name',
        'no_telp',
    ];

    public static function generateCode()
{
    // Get the latest customer based on ID (or code)
    $latestCustomer = self::orderBy('id', 'desc')->first();

    // Default starting number
    $nextNumber = 1;

    if ($latestCustomer && preg_match('/CUST-(\d+)/', $latestCustomer->code, $matches)) {
        $nextNumber = (int) $matches[1] + 1;
    }

    // Pad the number to 10 digits and return
    return 'CUST-' . str_pad($nextNumber, 10, '0', STR_PAD_LEFT);
}

}
