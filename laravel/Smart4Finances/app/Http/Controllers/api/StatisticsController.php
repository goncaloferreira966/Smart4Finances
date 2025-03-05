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
            'user_types' => User::selectRaw('type, COUNT(*) as count')->groupBy('type')->get(),
            //'top_user' => User::orderByDesc('value')->first(['nickname', 'value']),
            'users_with_avatar' => User::whereNotNull('photo_filename')->count(),
            'users_without_avatar' => User::whereNull('photo_filename')->count(),
           
            'last_deleted_user' => User::onlyTrashed()->orderByDesc('deleted_at')->first()
        ];
        
        return response()->json($statistics);
    }
}
