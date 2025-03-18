<?php
// Importação dos controllers da API
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendReport;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\StatisticsController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
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
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

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

    Route::post('/send-email', function (Request $request) {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('pdfs', 'Smart4Finances_Relatório_Financeiro.pdf', 'local');        
    
            Mail::to(auth()->user()->email)->send(new SendReport($path));
    
            return response()->json(["message" => "Email enviado com sucesso!"]);
        }
    
        return response()->json(["error" => "Arquivo não encontrado"], 400);
    });

    // Endpoints para usuários (DAD)
    Route::post('user/{id}/updateProfile', [UserController::class, 'update']);
    Route::post('logout',  [AuthController::class, 'logout']);
    Route::patch('/users/{userId}/block', [UserController::class, 'updateBlocked']);
    Route::get('/users/{userId}/nickname', [UserController::class, 'getUserNickname']);
    Route::apiResource("users", UserController::class);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refreshtoken', [AuthController::class, 'refreshToken']);
    //Route::post('/users/me', [UserController::class, 'showMe']);

    // Endpoints para Categorias
    Route::apiResource("categories", CategoryController::class);

    // Endpoints para Despesas
    Route::apiResource("expenses", ExpenseController::class);

    Route::apiResource('incomes', IncomeController::class);

    //Endpoints para dashboard do admin
    Route::get('/admin-statistics', [StatisticsController::class, 'index']);

    //Endpoints para dashboard do Cliente
    Route::get('/client-statistics', [StatisticsController::class, 'clientStatistics']);

});
