<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvestmentController extends Controller
{
    // Lista todas as despesas do usuário autenticado (opcional)
    public function index(Request $request)
    {
        // Inicia a query com as despesas do usuário autenticado
        $query = Investment::where('user_id', auth()->id());

        // Filtra pela data de início, se informado
        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        // Filtra pela data de fim, se informado
        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }

        // Filtra pelo preço mínimo, se informado
        if ($request->filled('minPrice')) {
            $query->where('amount', '>=', $request->minPrice);
        }
        
        // Filtra pelo preço máximo, se informado
        if ($request->filled('maxPrice')) {
            $query->where('amount', '<=', $request->maxPrice);
        }

        // Se desejar, pode aplicar paginação:
        $investments = $query->paginate($request->get('perPage', 10));

        return response()->json($investments);
    }




    // Exibe os detalhes de uma despesa
    public function show($id)
    {
        $investment = Investment::find($id);
        if (!$investment) {
            return response()->json(['error' => 'Investimento não encontrado'], 404);
        }
        return response()->json($investment);
    }

   


    // Remove uma despesa
    public function destroy($id)
    {
        $investment = Investment::find($id);
        if (!$investment) {
            return response()->json(['error' => 'Investimento não encontrado'], 404);
        }

        $investment->delete();

        return response()->json(['message' => 'Investimento removido com sucesso']);
    }


    // Cria um novo investimento
    public function store(Request $request)
    {
        // Validação dos dados
        $request->validate([
            'type'  => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'roi'    => 'nullable|numeric|min:0|max:100'
        ]);

        // Obtém os dados do request
        $data = $request->all();
        $data['user_id'] = auth()->id();

        // Cria o investimento
        $investment = Investment::create($data);

        return response()->json($investment, 201);
    }

    // Atualiza um investimento existente
    public function update(Request $request, $id)
    {
        // Procura o investimento
        $investment = Investment::find($id);
        if (!$investment) {
            return response()->json(['error' => 'Investimento não encontrado'], 404);
        }

        // Verifica se o investimento pertence ao usuário logado
        if ($investment->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validação dos dados
        $request->validate([
            'type'  => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'roi'    => 'sometimes|numeric|min:0|max:100'
        ]);

        // Atualiza os dados informados
        $investment->update($request->only(['type', 'amount', 'roi', 'date']));

        return response()->json([
            'message' => 'Investimento atualizado com sucesso!',
            'investment' => $investment,
        ], 200);
    }
}
