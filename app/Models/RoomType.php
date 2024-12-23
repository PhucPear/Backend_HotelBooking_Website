<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $primaryKey = 'Type_ID';
    protected $fillable = [
        'Name',
        'Price',
        'Capacity',
    ];

    public $timestamps = false;

    public function rooms()
    {
        return $this->hasMany(Room::class, 'Type_ID', 'Type_ID');
    }
}
