<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;


const PASSPORT_SERVER_URL = "http://localhost"; //Local server
//const PASSPORT_SERVER_URL = "http://api-dad-group23.172.22.21.101.sslip.io"; //School server
const CLIENT_ID = 2; //Local Client Server ID
//const CLIENT_ID = 1; //School Client Server ID
const CLIENT_SECRET = 'yyAilfD1XrJyhBgZfNMiAjanMK4cRRYs6sJ4UNZU'; //Gonçalo Auth Server Token
//const CLIENT_SECRET = 'fUGPpc9nDom6FJvlljfMan33eGkFuC4jOSEV6diX'; //School Auth Server Token

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

        // Verificar se o usuário existe e está bloqueado
        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], 404);
        }
    
        // Se o usuário estiver bloqueado, retornar erro sem interferir no fluxo de autenticação
        if ($user->blocked) {
            return response()->json(['message' => 'Conta bloqueada'], 403); // Retornar 403 para contas bloqueadas
        }
    

        try {
            request()->request->add($this->passportAuthenticationData(
            $request->username, $request->password));
            $request = Request::create(PASSPORT_SERVER_URL . '/oauth/token', 'POST');
            $response = Route::dispatch($request);
            $errorCode = $response->getStatusCode();
            $auth_server_response = json_decode((string) $response->content(), true);             
            return response()->json($auth_server_response, $errorCode);
        }
        catch (\Exception $e) {
            return response()->json('Authentication has failed!', 401);
        }
    }

    public function logout(Request $request)
    {
        $accessToken = $request->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        $token->delete();
        return response(['msg' => 'Token revoked'], 200);
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
