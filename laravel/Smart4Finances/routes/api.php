<?php
// Importação dos controllers da API
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\GameController;
use App\Http\Controllers\api\StatisticsController;
use App\Http\Controllers\api\TransactionsController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\ExpenseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui você registra as rotas da API para a sua aplicação. Essas rotas
| são carregadas pelo RouteServiceProvider e todas estão no grupo "api".
|
*/

// Rotas públicas
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/usersPost', [UserController::class, 'store']);

// Rotas protegidas por middleware de autenticação
Route::middleware(['auth:api'])->group(function () {
    // Endpoints para notificações
    Route::get('/notifications', function (Request $request) {
        return response()->json([
            'notifications' => $request->user()->notifications,
        ]);
    });

    Route::get('/notifications/unread', function (Request $request) {
        return response()->json([
            'unread_notifications' => $request->user()->unreadNotifications,
        ]);
    });

    Route::post('/notifications/{id}/mark-as-read', function ($id) {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read.']);
        }
        return response()->json(['error' => 'Notification not found.'], 404);
    });

    // Endpoints para usuários (DAD)
    Route::post('user/{id}/updateProfile', [UserController::class, 'update']);
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::patch('/users/{userId}/block', [UserController::class, 'updateBlocked']);
    Route::get('/users/{userId}/nickname', [UserController::class, 'getUserNickname']);
    Route::apiResource("users", UserController::class);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    Route::post('/users/me', [UserController::class, 'showMe']);

    // Endpoints para Categorias
    Route::apiResource("categories", CategoryController::class);

    // Endpoints para Despesas
    Route::apiResource("expenses", ExpenseController::class);
});
