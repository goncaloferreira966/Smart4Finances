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
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'E-mail já confirmado.'], 400);
        }

        // Gerar o URL com assinatura
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $request->user()->id]
        );

        // Enviar diretamente com Mailable
        Mail::to($request->user()->email)->send(new ConfirmEmailMailable($request->user(), $url));

        return response()->json(['message' => 'Novo e-mail de verificação enviado com sucesso!']);
    }
}
