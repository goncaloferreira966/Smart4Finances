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
use App\Notifications\EmailConfirmationNotification;
class UserController extends Controller
{
    public function update(Request $request, $id)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $id) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'nickname' => 'nullable|string|max:20|unique:users,nickname,' . $id,
            'email' => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:3', // Apenas se a senha for informada
            'photo_filename' => 'nullable|file|mimes:jpeg,png,jpg|max:10240', // Aceita imagens de até 10MB
            'coin' => 'nullable|in:$,€,R$,£',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        //\Log::info('Chegou à função update', $request->all());


        try {
            // Encontrar o user
            $user = User::findOrFail($id);

            // Atualizar os campos
            if ($request->hasFile('photo_filename') && $request->file('photo_filename')->isValid()) {
                // Armazenar a imagem no diretório public/photos
                $photoPath = $request->file('photo_filename')->store('photos', 'public');
                $user->photo_filename = basename($photoPath); // Atualizar o nome do arquivo
            }

            // Atualizar os campos básicos
            if ($request->input('name')) {
                $user->name = $request->input('name');
            }

            if ($request->input('nickname')) {
                $user->nickname = $request->input('nickname');
            }
            if ($request->input('email')) {
                $user->email = $request->input('email');
            }
        
            // Se a password for fornecida, atualiza a senha
            if ($request->input('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            // Atualizar o campo coin, se fornecido
            if ($request->has('coin')) {
                $user->coin = $request->input('coin');
            }

            // Guardar as alterações
            $user->notify(new CustomNotification("Parabéns! Você editou o seu perfil com Sucesso!"));

            $user->save();

            return response()->json([
                'message' => 'Utilizador atualizado com sucesso!',
                'user' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar utilizador',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {   
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'nickname' => 'required|string|max:20|unique:users,nickname',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:3',
            'photo_filename' => 'nullable|file|mimes:jpeg,png,jpg|max:10240',
            'value' => 'nullable|integer|min:0',
            'blocked' => 'nullable|boolean',
            'custom' => 'nullable|json',
            'coin' => 'nullable|in:$,€,R$,£',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $photoPath = null;
            if ($request->hasFile('photo_filename') && $request->file('photo_filename')->isValid()) {
                $photoPath = $request->file('photo_filename')->store('photos', 'public');
            }

            $isFirstUser = User::count() === 0;

            $user = User::create([
                'name' => $request->input('name'),
                'coin' => $request->input('coin', '$'),
                'nickname' => $request->input('nickname'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'type' => $isFirstUser ? 'A' : 'C',
                'photo_filename' => $photoPath ? basename($photoPath) : null,
                'value' => $request->input('value', 0),
                'blocked' => $request->input('blocked', 0),
                'custom' => $request->input('custom', null),
            ]);

            // Aqui é onde deves disparar a notificação
            $user->notify(new EmailConfirmationNotification());

            return response()->json([
                'message' => 'Utilizador criado com sucesso! Verifique o seu e-mail para confirmar a conta.',
                'user' => $user,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar utilizador',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function storeBase64AsFile(User $user, String $base64String)
    {
        $targetDir = storage_path('app/public/fotos');
        $newfilename = $user->id . "_" . rand(1000,9999);
        $base64Service = new Base64Services();
        return $base64Service->saveFile($base64String, $targetDir, $newfilename);
    }

    public function getUser(User $user)
    {
        return new UserResource($user);
    }

    public function show($id)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $id && $user->type == 'C') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        $user = User::find($id); // Isso busca pelo ID

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function getAllUsers()
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->type == 'C') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END
        $users = User::whereNull('deleted_at')->get();
    }
    
    public function index(Request $request)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->type == 'C') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END
        
        $query = User::query();

        if ($request->has('type') && in_array($request->type, ['A', 'C'])) {
            $query->where('type', $request->type);
        }

        return UserResource::collection($query->paginate(20));
    }

    public function destroy($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId && $user->type == 'C') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        $user = User::find($userId);
    
        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], Response::HTTP_NOT_FOUND);
        }
        //now it's a soft delete
        try {
            $user->blocked = true;
            $user->deleted_at = Carbon::now(); // Define a data e hora atual
            $user->save();
            return response()->json(['message' => 'User eliminado com sucesso'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao eliminar user', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    public function updateBlocked(Request $request, $userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->type == 'C') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        // Valida a entrada para garantir que o campo blocked seja booleano
        $request->validate([
            'blocked' => 'required|boolean',
        ]);

        // Busca o User pelo ID fornecido
        $user = User::find($userId);

        // Verifica se o User foi encontrado
        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Atualiza o campo blocked
        $user->blocked = $request->blocked;
        $user->save();

        // Retorna a resposta de sucesso
        return response()->json(['message' => 'Status de bloqueio atualizado com sucesso', 'user' => $user], Response::HTTP_OK);
    }

    public function getUserRole($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], 404);
        }

        return response()->json(['type' => $user->type]);
    }

    public function getUserNickname($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User não encontrado'], 404);
        }

        return response()->json(['nickname' => $user->nickname]);
    }

    public function showMe(Request $request)
    {
        return new UserResource($request->user());
    }
}
