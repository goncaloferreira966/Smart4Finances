<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\ConfirmEmailMailable;

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
        $request->validate(['email' => 'required|email']);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'E-mail não encontrado.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já confirmado.'], 400);
        }

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id]
        );

        Mail::to($user->email)->send(new ConfirmEmailMailable($user, $url));

        return response()->json(['message' => 'Novo e-mail de verificação enviado com sucesso!']);
    }

}
