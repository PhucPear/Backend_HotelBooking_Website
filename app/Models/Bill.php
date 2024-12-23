<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $primaryKey = 'Bill_ID';
    public $timestamps = false;

    protected $fillable = [
        'Date',
        'Amount',
        'Payments',
        'Employee_ID',
        'Customer_ID',
        'Status',
    ];

    public function details()
    {
        return $this->hasMany(DetailsBill::class, 'Bill_ID', 'Bill_ID');
    }

    /**
     * Tính tổng số tiền từ các DetailsBill và cập nhật vào cột Amount
     */
    public function calculateTotalAmount()
    {
        $totalAmount = $this->details->sum(function ($detail) {
            return $detail->room->roomType->Price;
        });

        $this->Amount = $totalAmount;
        $this->save();

        return $this->Amount;
    }
}
