<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\FirebaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Routing\RequestContext;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('locations', [LocationController::class, 'index']);
//Route::get('get-firebase-data', [FirebaseController::class, 'index'])->name('firebase.index');


Route::middleware('auth:api')->group(function () {

    Route::get('logout', [AuthController::class, 'logout']);

    Route::get('customers/{customer}',[CustomerController::class,'show']);

    Route::get('cars/{car}',[CarController::class,'show']);

    Route::get('drivers/{driver}', [DriverController::class, 'show']);

    Route::post('locations', [LocationController::class, 'store']);

    
    Route::get('orders', [OrderController::class, 'index']);
    Route::delete('orders/{order}', [OrderController::class, 'destroy']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/invisible/{order}', [OrderController::class, 'invisible']);
    Route::post('orders/calculate/', [OrderController::class, 'calculate']);

    
    Route::post('trips/calculate/', [TripController::class, 'calculate']);
   

    Route::get('trips', [TripController::class, 'index']);
    Route::get('trips/submit/{trip}', [TripController::class, 'submit']);
    Route::post('trips', [TripController::class, 'store']);
    Route::post('trips/invisible/{trip}', [TripController::class, 'invisible']);
    
    Route::post('orders/edit/{order}', [OrderController::class, 'update']);
    Route::post('trips/edit/{trip}', [TripController::class, 'update']);


    Route::middleware('customer')->group(function () {
        Route::post('orders', [OrderController::class, 'store']);
        //Route::post('orders/edit/{order}', [OrderController::class, 'update']);
        Route::get('orders/submit/{order}', [OrderController::class, 'submit']);
        Route::get('orders/invisibleAsCustomer/{order}', [OrderController::class, 'invisibleAsCustomer']);
        
        Route::get('trips/submit/{trip}', [TripController::class, 'submit']);
        Route::post('trips/book/{trip}', [TripController::class, 'book']);
        Route::get('trips/cancelAsCustomer/{trip}', [TripController::class, 'cancelAsCustomer']);
        Route::get('trips/unvisibleAsCustomer/{trip}', [TripController::class, 'unvisibleAsCustomer']);

    });

    Route::middleware('driver')->group(function(){
        Route::post('trips/accept/{trip}', [TripController::class, 'accept']);
        Route::get('trips/reject/{trip}', [TripController::class, 'reject']);
        Route::get('trips/begin/{trip}', [TripController::class, 'begin']);
        Route::get('trips/end/{trip}', [TripController::class, 'end']);
        Route::get('trips/payment/{trip}', [TripController::class, 'assurePayment']);
        Route::get('trips/submit/{trip}', [TripController::class, 'submit']);
        Route::get('trips/cancelAsDriver/{trip}', [TripController::class, 'cancelAsDriver']);
        Route::get('trips/unvisibleAsDriver/{trip}', [TripController::class, 'unvisibleAsDriver']);



        Route::get('drivers/setAvailabilityFalse/', [DriverController::class, 'setAvailabilityFalse']);
        Route::get('drivers/setAvailabilityTrue/', [DriverController::class, 'setAvailabilityTrue']);


        Route::get('orders/accept/{order}', [OrderController::class, 'accept']);
        Route::get('orders/reject/{order}', [OrderController::class, 'reject']);
        Route::get('orders{order}', [OrderController::class, 'reject']);
        Route::get('orders/begin/{order}', [OrderController::class, 'begin']);
        Route::get('orders/end/{order}', [OrderController::class, 'end']);
        Route::get('orders/payment/{order}', [OrderController::class, 'assurePayment']);
        Route::get('orders/invisibleAsDriver/{order}', [OrderController::class, 'invisibleAsDriver']);


    });

    Route::middleware('supAdmin')->group(function () {
        Route::get('admins', [AdminController::class, 'index']);
        Route::put('admins/{admin}', [AdminController::class, 'update']);
        Route::get('admins/{admin}', [AdminController::class, 'show']);
        Route::delete('admins/{admin}', [AdminController::class, 'destroy']);
        Route::post('admins', [AdminController::class, 'store']);

        Route::delete('users/{user}', [AuthController::class, 'destroy']);

    });

    Route::middleware('admin')->group(function () {

        Route::post('drivers', [DriverController::class, 'store']);
        Route::get('drivers', [DriverController::class, 'index']);
        Route::delete('drivers/{driver}', [DriverController::class, 'destroy']);
        Route::put('drivers/{driver}', [DriverController::class, 'update']);

        Route::get('users', [AuthController::class, 'index']);
        
        Route::get('cars', [CarController::class, 'index']);
        Route::post('cars', [CarController::class, 'store']);
        Route::put('cars/{car}', [CarController::class, 'update']);
        Route::delete('cars/{car}', [CarController::class, 'destroy']);

        Route::get('customers',[CustomerController::class,'index']);
        Route::delete('customers/{customer}',[CustomerController::class,'destroy']);

        Route::delete('trips/{trip}',[TripController::class,'destroy']);


    });
});

 Route::resources([
 //    'admins' => AdminController::class,
//     'cars' => CarController::class,
//     'complaints' => ComplaintController::class,
//     'customers' => CustomerController::class,
//     'drivers' => DriverController::class,
//     //'orders' => OrderController::class,
//     'trips' => TripController::class,
//     'users' => AuthController::class
 ]);
