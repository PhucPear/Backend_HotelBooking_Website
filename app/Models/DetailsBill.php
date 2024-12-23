<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailsBill extends Model
{
    use HasFactory;

    protected $primaryKey = 'Bill_ID';
    public $timestamps = false;

    protected $fillable = [
        'Bill_ID',
        'Order',
        'Room_ID',
        'Check_in_Date',
        'Check_out_Date',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'Bill_ID', 'Bill_ID');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'Room_ID', 'Room_ID');
    }
}
