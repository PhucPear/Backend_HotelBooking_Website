<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DetailsBillController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomTypeController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\AuthController as AuthUserController;
use App\Http\Controllers\User\BookingController;
use App\Http\Controllers\User\PaymentsController;

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
//ADMIN
Route::prefix('admin')->group(function () {
    //Login
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::get('auth/search', [AuthController::class, 'searchAuth']);
    Route::resource('auth', AuthController::class);

    Route::get('employees/search', [EmployeeController::class, 'searchEmployee']);
    Route::resource('employees', EmployeeController::class);

    Route::get('room-types/search', [RoomTypeController::class, 'searchRoomType']);
    Route::resource('room-types', RoomTypeController::class);

    Route::get('rooms/search', [RoomController::class, 'searchRoom']);
    Route::resource('rooms', RoomController::class);

    Route::get('customers/search', [CustomerController::class, 'searchCustomer']);
    Route::resource('customers', CustomerController::class);

    Route::resource('details-bills', DetailsBillController::class);
    Route::post('details-bills/delete-details-bill', [DetailsBillController::class, 'deleteDetailsBill']);

    Route::get('bills/search', [BillController::class, 'searchBill']);
    Route::get('bills/getDetailsBill/{billID}', [BillController::class, 'getDetailsBill']);
    Route::resource('bills', BillController::class);
});
//USER
Route::prefix('user')->group(function () {
    //Login
    Route::post('send-otp', [AuthUserController::class, 'sendOtp']);
    Route::post('login', [AuthUserController::class, 'login']);
    Route::post('logout', [AuthUserController::class, 'logout']);
    Route::post('register', [AuthUserController::class, 'register']);

    Route::get('rooms', [HomeController::class, 'index']);
    Route::get('search', [HomeController::class, 'searchRoom']);
    Route::get('get-customer', [HomeController::class, 'getCustomer']);
    Route::get('find', [HomeController::class, 'findRoom']);
    Route::post('booking-room', [BookingController::class, 'bookingRoom']);
    Route::get('booking-history', [BookingController::class, 'bookingHistory']);
    //Payments
    Route::post('/create-payment', [PaymentsController::class, 'createPayment']);
    Route::post('/payment-return', [PaymentsController::class, 'paymentReturn']);
});
