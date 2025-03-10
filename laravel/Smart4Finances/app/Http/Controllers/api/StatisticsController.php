<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Services\Base64Services;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CustomNotification;

class StatisticsController extends Controller
{
    public function index()
    {
        $statistics = [
            'total_users' => User::count(),
            'active_users' => User::where('blocked', false)->count(),
            'blocked_users' => User::where('blocked', true)->count(),
            'deleted_users' => User::onlyTrashed()->count(),
            'adminCount' => User::where('type', 'A')->count(), // Contagem de Admins
            'clientCount' => User::where('type', 'C')->count(), // Contagem de Clients
            //'top_user' => User::orderByDesc('value')->first(['nickname', 'value']),
            'users_with_avatar' => User::whereNotNull('photo_filename')->count(),
            'users_without_avatar' => User::whereNull('photo_filename')->count(),
           
            'last_deleted_user' => User::onlyTrashed()->orderByDesc('deleted_at')->first()
        ];
        
        return response()->json($statistics);
    }

    public function clientStatistics(Request $request)
    {
        $user = auth()->user();
        
        $year = $request->input('year', now()->year);
        $month = $request->input('month', null);
        
        $queryIncome = Income::where('user_id', $user->id)->whereYear('date', $year);
        $queryExpense = Expense::where('user_id', $user->id)->whereYear('date', $year);
        $queryInvestment = Investment::where('user_id', $user->id)->whereYear('created_at', $year);
        
        if ($month) {
            $queryIncome->whereMonth('date', $month);
            $queryExpense->whereMonth('date', $month);
            $queryInvestment->whereMonth('created_at', $month);
        }
        
        $incomeByMonth = $queryIncome->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
        
        $expenseByMonth = $queryExpense->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
        
        $expensesByCategory = $queryExpense->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, SUM(expenses.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->pluck('total', 'name');
        
        $investmentTrend = $queryInvestment->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');
        
        return response()->json([
            'incomeByMonth' => $incomeByMonth,
            'expenseByMonth' => $expenseByMonth,
            'expensesByCategory' => $expensesByCategory,
            'investmentTrend' => $investmentTrend,
        ]);
    }

}
