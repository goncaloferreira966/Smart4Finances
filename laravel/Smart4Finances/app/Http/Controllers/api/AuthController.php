<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;


const PASSPORT_SERVER_URL = "http://localhost"; //Local server
const CLIENT_ID = 2; //Local Client Server ID
//const CLIENT_SECRET = 'vgxKSIVMTffFMa9FjhYJZZS1yRZ8y95Vnd7nA4wj'; //Gonçalo Auth Server Token
//const CLIENT_SECRET = 'KKS9CsEfqubEYpv0elG8LOMGafFFVFg4kSTPXKaK'; //Cláudio Auth Server Token
const CLIENT_SECRET = 'dMPIFR31VYicnDjxl9d9r409rDIFdOMWgFBXTyGs'; //CláudioServer Auth Server Token


class AuthController extends Controller
{
    //
    private function passportAuthenticationData($username, $password) {
        return [
        'grant_type' => 'password',
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
        'username' => $username,
        'password' => $password,
        'scope' => ''
        ];
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->username)->first();

        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], 404);
        }

        if ($user->blocked) {
            return response()->json(['message' => 'Conta bloqueada'], 403);
        }

        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Email não verificado. Por favor, verifique o seu e-mail para confirmar a conta.'], 402);
        }

        try {
            request()->request->add($this->passportAuthenticationData($request->username, $request->password));
            $proxyRequest = Request::create(PASSPORT_SERVER_URL . '/oauth/token', 'POST');
            $passportResponse = Route::dispatch($proxyRequest);
            $errorCode = $passportResponse->getStatusCode();
            $auth_server_response = json_decode((string) $passportResponse->content(), true);

            // Só adiciona o email_verified_at
            if ($errorCode === 200) {
                $auth_server_response['email_verified_at'] = $user->email_verified_at;
            }

            return response()->json($auth_server_response, $errorCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication has failed!',
                'error' => $e->getMessage()
            ], 401);
        }   
    }



    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $accessToken->revoke();
        return response(['msg' => 'Token revogado com sucesso!'], 200);
    }

    private function revokeCurrentToken(User $user)
    {
        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', $currentTokenId)->delete();
    }

    public function refreshToken(Request $request)
    {
        // Revokes current token and creates a new token
        $this->purgeExpiredTokens();
        $this->revokeCurrentToken($request->user());
        $token = $request->user()->createToken('authToken', ['*'],
        now()->addHours(2))->plainTextToken;
        return response()->json(['token' => $token]);
    }
}
