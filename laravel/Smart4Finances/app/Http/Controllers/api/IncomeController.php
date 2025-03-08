<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncomeController extends Controller
{
    // Lista todas as receitas do utilizador autenticado
    public function index(Request $request)
    {
        // Inicia a query com as receitas do utilizador autenticado
        $query = Income::where('user_id', auth()->id());

        // Filtra pela fonte, se informada
        if ($request->filled('source')) {
            $query->where('source', 'like', '%' . $request->source . '%');
        }

        // Filtra pelo montante mínimo, se informado
        if ($request->filled('minAmount')) {
            $query->where('amount', '>=', $request->minAmount);
        }

        // Filtra pelo montante máximo, se informado
        if ($request->filled('maxAmount')) {
            $query->where('amount', '<=', $request->maxAmount);
        }

        // Filtra pela data de início, se informada
        if ($request->filled('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }

        // Filtra pela data de fim, se informada
        if ($request->filled('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        // Aplica paginação (por defeito 10 itens por página)
        $incomes = $query->paginate($request->get('perPage', 10));

        return response()->json($incomes);
    }

    // Cria uma nova receita
    public function store(Request $request)
    {
        // Validação dos campos obrigatórios
        $request->validate([
            'source' => 'required|string|max:255',
            'date'   => 'required|date',
            'amount' => 'required|numeric',
        ]);

        // Obtém os dados do request
        $data = $request->all();

        // Se o campo de intervalo recorrente for "0", define como null (não recorrente)
        if (isset($data['recurring_interval']) && $data['recurring_interval'] === "0") {
            $data['recurring_interval'] = null;
        }
        if (isset($data['recurring_interval_unit']) && $data['recurring_interval_unit'] === "0") {
            $data['recurring_interval_unit'] = null;
        }

        // Associa a receita ao utilizador autenticado
        $data['user_id'] = auth()->id();

        // Trata o upload do recibo, se enviado
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt'] = $path;
        }

        $income = Income::create($data);

        return response()->json($income, 201);
    }

    // Exibe os detalhes de uma receita
    public function show($id)
    {
        $income = Income::find($id);
        if (!$income) {
            return response()->json(['error' => 'Receita não encontrada'], 404);
        }
        return response()->json($income);
    }

    // Atualiza uma receita existente
    public function update(Request $request, $id)
    {
        $income = Income::find($id);
        if (!$income) {
            return response()->json(['error' => 'Receita não encontrada'], 404);
        }

        $request->validate([
            'source' => 'sometimes|required|string|max:255',
            'date'   => 'required|date',
            'amount' => 'required|numeric',
        ]);

        if ($request->input('recurring_interval') === "0") {
            $request->merge([
                'recurring_interval'      => null,
                'recurring_interval_unit' => null,
            ]);
        }

        // Trata o upload do recibo, se enviado
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $request->merge(['receipt' => $path]);
        }

        // Atualiza a receita com os dados recebidos
        $income->update($request->all());

        return response()->json($income);
    }

    // Remove uma receita
    public function destroy($id)
    {
        $income = Income::find($id);
        if (!$income) {
            return response()->json(['error' => 'Receita não encontrada'], 404);
        }

        $income->delete();

        return response()->json(['message' => 'Receita removida com sucesso']);
    }
}
