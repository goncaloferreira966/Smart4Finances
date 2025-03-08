<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    // Lista todas as despesas do usuário autenticado (opcional)
    public function index(Request $request)
    {
        // Inicia a query com as despesas do usuário autenticado
        $query = Expense::where('user_id', auth()->id());

        // Filtra por categoria, se informado
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filtra pelo preço mínimo, se informado
        if ($request->filled('minPrice')) {
            $query->where('amount', '>=', $request->minPrice);
        }

        // Filtra pelo preço máximo, se informado
        if ($request->filled('maxPrice')) {
            $query->where('amount', '<=', $request->maxPrice);
        }

        // Filtra pela data de início, se informado
        if ($request->filled('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }

        // Filtra pela data de fim, se informado
        if ($request->filled('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }

        // Se desejar, pode aplicar paginação:
        $expenses = $query->paginate($request->get('perPage', 10));

        return response()->json($expenses);
    }


    // Cria uma nova despesa
    public function store(Request $request)
    {
        // Valida os campos obrigatórios
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'date'        => 'required|date',
            'amount'      => 'required|numeric',
        ]);

        // Obtém os dados do request em um array
        $data = $request->all();

        // Se o campo de intervalo for "0", define como null (não recorrente)
        if (isset($data['recurring_interval']) && $data['recurring_interval'] === "0") {
            $data['recurring_interval'] = null;
        }

        // Associa a despesa ao usuário autenticado
        $data['user_id'] = auth()->id();

        // Trata o upload do recibo, se enviado, sem fazer merge no request
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt'] = $path;
        }

        $expense = Expense::create($data);

        return response()->json($expense, 201);
    }


    // Exibe os detalhes de uma despesa
    public function show($id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json(['error' => 'Despesa não encontrada'], 404);
        }
        return response()->json($expense);
    }

    // Atualiza uma despesa existente
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json(['error' => 'Despesa não encontrada'], 404);
        }

        $request->validate([
            'date'   => 'required|date',
            'amount' => 'required|numeric',
        ]);

        if ($request->input('recurring_interval') === "0") {
            $request->merge([
                'recurring_interval'      => null,
                'recurring_interval_unit' => null,
            ]);
        }

        // Se houver novo upload, trata a atualização do recibo
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $request->merge(['receipt' => $path]);
        }

        // Atualiza a despesa com os dados recebidos
        $expense->update($request->all());

        return response()->json($expense);
    }

    // Remove uma despesa
    public function destroy($id)
    {
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json(['error' => 'Despesa não encontrada'], 404);
        }

        $expense->delete();

        return response()->json(['message' => 'Despesa removida com sucesso']);
    }
}
