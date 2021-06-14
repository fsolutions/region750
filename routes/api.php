<?php

use Illuminate\Http\Request;
use App\Bundles\User\UserLists;
use App\Bundles\Calendar\Calendar;
use App\Bundles\Notifications\SMSC;
use Illuminate\Support\Facades\Route;
use App\Bundles\Service\DadataService;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\API\Log\LogController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\Order\OrderController;
use App\Http\Controllers\API\Address\FlatController;
use App\Http\Controllers\API\PassportAuthController;
use App\Http\Controllers\API\History\HistoryController;
use App\Http\Controllers\API\User\UserCommentController;
use App\Http\Controllers\API\Contract\ContractController;
use App\Http\Controllers\API\Contract\EquipmentController;
use App\Http\Controllers\API\Contract\ContractTOController;
use App\Http\Controllers\API\Reference\ReferenceController;
use App\Http\Controllers\API\Notification\NotificationController;
use App\Http\Controllers\API\Prescription\PrescriptionController;
use App\Http\Controllers\API\Reference\ReferencePropertyController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);

Route::get('get-password/{user_id}', [SMSC::class, 'getPassword']);

// Route::post('password/forgot', [PassportAuthController::class, 'forgotPassword']);
// Route::post('password/reset', [PassportAuthController::class, 'resetPassword'])->name('password.reset');

Route::post('password-reset', [PassportAuthController::class, 'resetSMSPassword']);

Route::post('dadata/phone', [DadataService::class, 'cleanPhone']);
Route::post('dadata/company', [DadataService::class, 'getCompany']);
Route::post('dadata/address', [DadataService::class, 'getAddress']);
Route::post('dadata/name', [DadataService::class, 'getName']);
Route::post('dadata/email', [DadataService::class, 'getEmail']);

// Route::post('open/order/create', [OrderCreateExternal::class, 'store'])
//     ->middleware('checkIpForExternalOrderCreate');

Route::get('constants/roles', function () {
    return Config::get('constants.roles.options');
});


Route::middleware('auth:api')->group(function () {

    /**
     * Log
     */
    Route::get('logs', [LogController::class, 'index']);

    /**
     * History
     */
    Route::get('history', [HistoryController::class, 'index']);

    /**
     * Notifications
     */
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread', [NotificationController::class, 'getUnread']);
    Route::post('notifications/read/{id}', [NotificationController::class, 'readById']);
    Route::post('notifications/read-all-orders', [NotificationController::class, 'readForAllOrders']);
    Route::post('notifications/read-all', [NotificationController::class, 'readAll']);
    Route::delete('notifications/delete/{id}', [NotificationController::class, 'deleteById']);
    Route::post('notifications/delete-all', [NotificationController::class, 'deleteAll']);

    /**
     * UserComment
     */
    Route::get('comments/{field?}/{model_id?}', [UserCommentController::class, 'index']);
    Route::post('comments', [UserCommentController::class, 'store']);
    Route::put('comments/{id}', [UserCommentController::class, 'update']);
    Route::delete('comments/{id}', [UserCommentController::class, 'destroy']);

    /**
     * Users
     */
    Route::get('users/lists', [UserLists::class, 'userLists']);

    /**
     * Calendar
     */
    Route::get('calendar', [Calendar::class, 'index']);

    /**
     * ContractTO
     */
    Route::get('contracts-to/auto-to/{id}', [ContractTOController::class, 'autoTO']);

    /**
     * Orders
     */
    Route::put('orders/update-easy/{id}', [OrderController::class, 'updateEasy']);

    /**
     * ApiResource
     */
    Route::apiResource('references/properties', ReferencePropertyController::class);
    Route::apiResource('references', ReferenceController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('contracts', ContractController::class);
    Route::apiResource('contracts-to', ContractTOController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('prescriptions', PrescriptionController::class);
    Route::apiResource('equipment', EquipmentController::class);
    Route::apiResource('addresses/flats', FlatController::class);
    Route::apiResource('addresses/houses', FlatController::class);
    Route::apiResource('addresses/streets', FlatController::class);
    Route::apiResource('addresses/cities', FlatController::class);
    Route::apiResource('addresses/regions', FlatController::class);

    /**
     * Other routes
     */
    Route::get('/get-user', [PassportAuthController::class, 'getUser']);
    Route::post('/logout', [PassportAuthController::class, 'logout']);
    Route::post('/send-sms', [SMSC::class, 'sendSMSNotifyAction']);
});
