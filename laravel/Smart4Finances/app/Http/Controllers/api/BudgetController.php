<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    // Lista todos os orçamentos
    public function index(Request $request)
    {
        $query = Budget::with('category'); // Carrega a relação com a categoria
    
        // Filtra pelo usuário, se informado
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
    
        // Filtra pelo limite mínimo, se informado
        if ($request->filled('min_limit')) {
            $query->where('limit_amount', '>=', $request->min_limit);
        }
        
        // Filtra pelo limite máximo, se informado
        if ($request->filled('max_limit')) {
            $query->where('limit_amount', '<=', $request->max_limit);
        }
    
         // Filtra pela data de início, se informado
         if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }

        // Filtra pela data de fim, se informado
        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }
        // Paginação opcional
        $budgets = $query->paginate($request->get('perPage', 10));
    
        // Retorna os dados formatados, substituindo category_id pelo nome da categoria
        return response()->json($budgets->through(function ($budget) {
            return [
                'id' => $budget->id,
                'user_id' => $budget->user_id,
                'category' => $budget->category->name, // Retorna o nome da categoria
                'limit_amount' => $budget->limit_amount,
                'created_at' => $budget->created_at,
                'updated_at' => $budget->updated_at,
            ];
        }));
    }
    
    // Exibe os detalhes de um orçamento
    public function show($id)
    {
        $budget = Budget::with('category')->find($id);
    
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }

        // Obtém o primeiro e último dia do mês atual
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Soma das despesas associadas a este orçamento durante o mês atual
        $totalExpenses = Expense::where('category_id', $budget->category_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
    
        return response()->json([
            'id' => $budget->id,
            'user_id' => $budget->user_id,
            'category' => $budget->category->name, // Retorna o nome da categoria
            'limit_amount' => $budget->limit_amount,
            'created_at' => $budget->created_at,
            'updated_at' => $budget->updated_at,
            'total_expenses_this_month' => $totalExpenses,
        ]);
    }

    // Cria um novo orçamento
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:0',
        ]);

        // Define automaticamente o user_id do usuário autenticado
        $budgetData = $request->all();
        $budgetData['user_id'] = auth()->id();

        $budget = Budget::create($budgetData);

        return response()->json($budget, 201);
    }

    // Atualiza um orçamento existente
    public function update(Request $request, $id)
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }

        $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:categories,id',
            'limit_amount' => 'sometimes|numeric|min:0',
        ]);

        $budget->update($request->only(['user_id', 'category_id', 'limit_amount']));

        return response()->json([
            'message' => 'Orçamento atualizado com sucesso!',
            'budget' => $budget,
        ], 200);
    }

    // Remove um orçamento
    public function destroy($id)
    {
        $budget = Budget::find($id);
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }

        $budget->delete();

        return response()->json(['message' => 'Orçamento removido com sucesso']);
    }
}