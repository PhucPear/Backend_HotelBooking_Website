<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;


class PaymentsController extends Controller
{
    public function createPayment($bill, $returnUrl)
    {
        $vnp_TmnCode = Config::get('vnpay.vnp_TmnCode');
        $vnp_HashSecret = Config::get('vnpay.vnp_HashSecret');
        $vnp_Url = Config::get('vnpay.vnp_Url');
        $vnp_Returnurl = $returnUrl;
        $vnp_TxnRef = $bill->Bill_ID;
        $vnp_OrderInfo = "Payment for Bills" . $bill->Bill_ID;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $bill->Amount * 10000; // Số tiền thanh toán (VNĐ)
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    public function paymentReturn(Request $request)
    {
        $vnp_TxnRef = $request->vnp_TxnRef;
        $vnp_ResponseCode = $request->vnp_ResponseCode;
        $vnp_SecureHash = $request->vnp_SecureHash;

        $bill = Bill::where('Bill_ID', $vnp_TxnRef)->first();
        if (!$bill) {
            return response()->json(['status' => false, 'message' => 'Bill not found'], 404);
        }

        $vnp_HashSecret = Config::get('vnpay.vnp_HashSecret');
        $vnp_Params = $request->all();
        ksort($vnp_Params);
        $hashData = '';
        foreach ($vnp_Params as $key => $value) {
            if (substr($key, 0, 4) === 'vnp_') {
                $hashData .= urlencode($key) . '=' . urlencode($value) . '&';
            }
        }
        $hashData = rtrim($hashData, '&');
        $vnp_SecureHashCheck = hash('sha256', $hashData . $vnp_HashSecret);

        if ($vnp_ResponseCode == '00' && $vnp_SecureHash === $vnp_SecureHashCheck) {
            $bill->Status = 'Paid';
            $bill->save();

            return response()->json(['status' => true, 'message' => 'Payment Successful']);
        } else {
            return response()->json(['status' => false, 'message' => 'Payment Failed']);
        }
    }
}
