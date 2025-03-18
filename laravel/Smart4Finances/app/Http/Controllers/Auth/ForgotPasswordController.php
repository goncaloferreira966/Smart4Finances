<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Mail\CustomResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

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
}


