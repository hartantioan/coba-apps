<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    use HasFactory;

    protected $table = 'jobs'; // Replace with your actual table name
    protected $primaryKey = 'id';
    protected $fillable = ['job_id', 'status']; // Define the fillable columns

    // Define any additional methods you might need

    public function hasBeenProcessed()
    {
        return $this->status === 'done'; // Adjust this based on your status check logic
    }
}
