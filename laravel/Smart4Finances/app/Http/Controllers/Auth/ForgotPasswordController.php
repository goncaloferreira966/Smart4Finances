<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Mail\CustomResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'E-mail não encontrado.'], 404);
        }

        $token = \Illuminate\Support\Facades\Password::createToken($user);

        Mail::to($user->email)->send(new CustomResetPasswordMail($user, $token));

        return response()->json(['message' => 'E-mail de recuperação enviado com sucesso!']);
    }
    public function validateResetToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['valid' => false], 400);
        }

        $expiresAt = Carbon::parse($record->created_at)->addMinutes(config('auth.passwords.users.expire', 60));
        if (now()->greaterThan($expiresAt)) {
            return response()->json(['valid' => false, 'message' => 'Token expirado.'], 400);
        }

        return response()->json(['valid' => true]);
    }

}


