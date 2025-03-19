<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $request->hasValidSignature()) {
            return redirect('http://localhost:5173?email_verified=invalid');
        }

        if ($user->email_verified_at) {
            return redirect('http://localhost:5173?email_verified=already');
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect('http://localhost:5173?email_verified=true');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já confirmado.'], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Novo e-mail de verificação enviado com sucesso!']);
    }

}