<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $primaryKey = 'Employee_ID';
    protected $fillable = [
        'Full_Name',
        'Email',
        'Position',
    ];

    public $timestamps = false;
}
