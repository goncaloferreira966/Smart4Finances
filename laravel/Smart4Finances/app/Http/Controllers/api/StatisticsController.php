<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Game;
use Carbon\Carbon;
use App\Models\User;


class StatisticsController extends Controller
{
    public function index()
    {
        $statistics = [
            'total_players' => User::count(),
            'total_games_played' => Game::count(),
            'games_played_last_week' => Game::where('created_at', '>=', Carbon::now()->subWeek())->count(),
            'games_played_last_month' => Game::where('created_at', '>=', Carbon::now()->subMonth())->count(),
            'total_blocked_users' => User::where('blocked', 1)->count(),
            'total_unblocked_users' => User::where('blocked', 0)->count(),
            // Estatísticas de saldo de moedas
            'average_brain_coins_balance' => floatval(User::avg('brain_coins_balance')),
            'highest_brain_coins_balance' => User::max('brain_coins_balance'),
            'lowest_brain_coins_balance' => User::min('brain_coins_balance'),
            // Estatísticas de tempo
            'average_time_per_game' => floatval(Game::average('total_time')), // Tempo médio por jogo
            'total_time_played' => floatval(Game::sum('total_time')), // Tempo total jogado
            'shortest_game_time' => floatval(Game::min('total_time')), // Tempo mais curto de jogo
            'longest_game_time' => floatval(Game::max('total_time')), // Tempo mais longo de jogo
        ]; 
        return response()->json($statistics);
    }
}
