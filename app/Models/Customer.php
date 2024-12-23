<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $primaryKey = 'Customer_ID';
    protected $fillable = [
        'Full_Name',
        'Date_of_Birth',
        'Email',
        'Phone',
        'ID',
    ];

    public $timestamps = false;
}
