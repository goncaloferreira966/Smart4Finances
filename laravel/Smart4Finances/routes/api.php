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

//DAD
Route::post('/login', [AuthController::class, 'login']);
Route::get('/statistics', [StatisticsController::class, 'index']);
Route::post('/usersPlayers', [UserController::class, 'store']);
Route::get('/multiplayer/leaderTable', [GameController::class, 'getTop5PlayersByVictoriesByBoard']);//Este endpoint também é usado em DAD

//END

//Android TAES sem auth
Route::get('/android/leaderBoardDuration', [GameController::class, 'getTop3GamesByBoard']);//Este endpoint também é usado em DAD
Route::get('/android/leaderBoardMoves', [GameController::class, 'getTop3GamesByBoardMoves']);//Este endpoint também é usado em DAD

//END


Route::middleware(['auth:api'])->group(function () {
    //Android TAES
    Route::get('/android/{user_id}/transactions', [TransactionsController::class, 'getTransactionsByUserAndroid']);
    Route::get('/android/{user_id}/games', [GameController::class, 'getGamesByUserAndroid']);
    Route::get('/android/{userid}/getTop10GamesByUserByDuration', [GameController::class, 'getTop10GamesByUserByDuration']);
    Route::get('/android/{userid}/getTop10GamesByUserByMoves', [GameController::class, 'getTop10GamesByUserByMoves']);
    
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
    Route::get('/users/{userId}/topPersonalGamesDuration', [GameController::class, 'getTopGameByBoardAndUser']);
    Route::get('/users/{userId}/topPersonalGamesMoves', [GameController::class, 'getTopGameByBoardAndUserMoves']);
    Route::get('/users/{userId}/multiplayerResults', [GameController::class, 'getMultiplayerGameResults']);
    Route::get('/users/{userId}/role', [UserController::class, 'getUserRole']);
    Route::get('/users/{userId}/nickname', [UserController::class, 'getUserNickname']);
    Route::post('/transactions', [TransactionsController::class, 'store']);
    Route::get('/users/{user_id}/transactions', [TransactionsController::class, 'getTransactionsByUser']);
    Route::get('/users/transactions', [TransactionsController::class, 'getAllTransactions']);
    Route::get('/users/{userId}/coins', [UserController::class, 'getUserCoins']);
    Route::get('/user/{userId}/games/status/{status}', [GameController::class, 'getGamesByUserAndStatus']);
    Route::patch('/users/{userId}/block', [UserController::class, 'updateBlocked']);
    Route::delete('/users/{userId}', [UserController::class, 'destroy']);
    Route::get('/user/{userId}/games', [GameController::class, 'getGamesByUser']);
    Route::get('/user/games', [GameController::class, 'getAllGames']);
    Route::apiResource("users", UserController::class);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::post('/users/me', [UserController::class , 'showMe']);
    //Post Game
    Route::post('/games', [GameController::class, 'store']);
    //END
});


