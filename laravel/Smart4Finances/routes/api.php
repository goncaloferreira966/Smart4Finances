<?php
//import of controller of laravel API
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GameController;
use App\Http\Controllers\api\StatisticsController;
use App\Http\Controllers\api\TransactionsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/usersPost', [UserController::class, 'store']);




Route::middleware(['auth:api'])->group(function () {
    //Android TAES
   
    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'notifications' => $request->user()->notifications, // Todas as notificações
        ]);
    });

    // Endpoint para listar notificações não lidas
    Route::get('/notifications/unread', function (Request $request) {
        return response()->json([
            'unread_notifications' => $request->user()->unreadNotifications, // Apenas não lidas
        ]);
    });

    // Endpoint para marcar notificações como lidas
    Route::post('/notifications/{id}/mark-as-read', function ($id) {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read.']);
        }
        return response()->json(['error' => 'Notification not found.'], 404);
    });
    //END
    
    //DAD
    Route::post('user/{id}/updateProfile', [UserController::class, 'update']);
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::patch('/users/{userId}/block', [UserController::class, 'updateBlocked']);
    Route::get('/users/{userId}/nickname', [UserController::class, 'getUserNickname']);
    Route::apiResource("users", UserController::class);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::post('/users/me', [UserController::class , 'showMe']);
    //END
});


