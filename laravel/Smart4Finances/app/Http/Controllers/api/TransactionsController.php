<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CustomNotification;


class TransactionsController extends Controller
{
    public function getAllTransactions(Request $request)
    {
        // SECURITY
        // Obtém o usuário logado
        $user = auth()->user();
    
        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->type == 'P') {
            // Retorna Unauthorized caso o ID não coincida e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // SECURITY-END
    
        // Inicializa a query base
        $query = Transaction::orderBy('transaction_datetime', 'desc');
    
        // Aplica o filtro de datas, se presente
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('transaction_datetime', [$request->input('date_from'), $request->input('date_to')]);
        }
    
        // Pagina os resultados (aplica paginação à query já filtrada)
        $transactions = $query->paginate(6);
    
        // Verifica se há transações na base de dados
        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'Nenhuma transação encontrada'], 404);
        }
    
        return response()->json($transactions);
    }

    public function getTransactionsByUser($userId)
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

        $transactions = Transaction::where('user_id', $userId)
                                   ->orderBy('transaction_datetime', 'desc') // Ordena pela data mais recente
                                   ->paginate(4); // Paginação opcional

        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'Nenhuma transação encontrada'], 404);
        }
        return response()->json($transactions);
    }

    public function getTransactionsByUserAndroid($userId)
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

        $transactions = Transaction::where('user_id', $userId)
                                   ->orderBy('transaction_datetime', 'desc') // Ordena pela data mais recente
                                   ->paginate(200000);

        if ($transactions->isEmpty()) {
            return response()->json(['message' => 'Nenhuma transação encontrada'], 404);
        }
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'game_id' => 'nullable|exists:games,id',
            'type' => 'required|in:B,P,I',
            'euros' => 'nullable|numeric',
            'brain_coins' => 'required|numeric',
            'payment_type' => 'nullable|in:MBWAY,PAYPAL,IBAN,VISA',
            'payment_reference' => 'nullable|string'
        ]);

        // Atribui um valor padrão de 0 a euros caso não seja fornecido
        $euros = $validatedData['euros'] ?? 0;


        // Atualiza o saldo de Brain Coins do User
        $user = User::findOrFail($validatedData['user_id']);
        $user->brain_coins_balance += $validatedData['brain_coins'];
        $user->save();

        //Notificações
        if ($validatedData['brain_coins'] > 0) {
            if ($validatedData['type'] === 'B') { // Verifica se o tipo é 'B' (bônus)
                $user->notify(new CustomNotification("Parabéns! Você recebeu {$validatedData['brain_coins']} moedas bónus!"));
            } elseif ($validatedData['type'] === 'P') { // Verifica se o tipo é 'P' (compra)
                $user->notify(new CustomNotification("Você comprou {$validatedData['brain_coins']} moedas!"));
            }
        }
         //Notificações-END


        // Cria a transação
        $transaction = Transaction::create([
            'user_id' => $validatedData['user_id'],
            'game_id' => $validatedData['game_id'],
            'type' => $validatedData['type'],
            'euros' => $euros,
            'brain_coins' => $validatedData['brain_coins'],
            'payment_type' => $validatedData['payment_type'],
            'payment_reference' => $validatedData['payment_reference'],
            'transaction_datetime' => now(),
        ]);

        return response()->json([
            'message' => 'Transaction created successfully.',
            'transaction' => $transaction,
            'user' => $user
        ], 201);
    }

}
