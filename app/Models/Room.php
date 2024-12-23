<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $primaryKey = 'Room_ID';
    protected $fillable = [
        'Type_ID',
        'Status',
        'Image'
    ];

    public $timestamps = false;
    
    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'Type_ID', 'Type_ID');
    }
}
