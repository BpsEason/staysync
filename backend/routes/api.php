<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SeoContentController;
use App\Http\Controllers\CultureContentController;
use App\Http\Controllers\AuthController; # Assuming you have an AuthController
use App\Http\Controllers\IoTDeviceController; # Assuming you will create this

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

// Public routes (e.g., registration, login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Health Check for Docker Compose and Kubernetes
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Booking Management
    Route::apiResource('bookings', BookingController::class);
    // Add health check for BookingController specifically if needed
    Route::get('/bookings/health', function() {
        return response()->json(['status' => 'ok', 'service' => 'BookingController']);
    });

    // Property Management (assuming you'll add a PropertyController)
    Route::apiResource('properties', \App\Http\Controllers\PropertyController::class); # Assuming a PropertyController exists now

    // SEO Content Management
    Route::apiResource('seo-contents', SeoContentController::class);
    Route::get('seo-contents/{property_id}/{language}', [SeoContentController::class, 'show']);

    // Culture Content Management
    Route::apiResource('culture/contents', CultureContentController::class)->only(['index', 'store']);
    // Add show, update, destroy for culture content if needed
    
    // IoT Devices Management (assuming you will create this controller)
    Route::apiResource('iot-devices', IoTDeviceController::class); # Assuming IoTDeviceController exists

});
