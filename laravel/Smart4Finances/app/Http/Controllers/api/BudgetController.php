<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    // Lista todos os orçamentos do usuário logado
    public function index(Request $request)
    {
        $query = Budget::with('category') // Carrega a relação com a categoria
                       ->where('user_id', auth()->id()); // Filtra pelo usuário logado
    
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
    
    // Exibe os detalhes de um orçamento do usuário logado
    public function show($id)
    {
        $budget = Budget::with('category')
                        ->where('id', $id)
                        ->where('user_id', auth()->id())
                        ->first();
    
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }

        // Obtém o primeiro e último dia do mês atual
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Soma das despesas associadas a este orçamento durante o mês atual
        $totalExpenses = Expense::where('category_id', $budget->category_id)
            ->where('user_id', auth()->id()) // Filtra despesas do usuário logado
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');
    
        return response()->json([
            'id' => $budget->id,
            'user_id' => $budget->user_id,
            'category_id' => $budget->category->id, // Retorna o nome da categoria
            'category' => $budget->category->name, // Retorna o nome da categoria
            'limit_amount' => $budget->limit_amount,
            'created_at' => $budget->created_at,
            'updated_at' => $budget->updated_at,
            'total_expenses_this_month' => $totalExpenses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'limit_amount' => 'required|numeric|min:0',
        ]);
    
        $userId = auth()->id();
        $categoryId = $request->category_id;
    
        // Verifica se já existe um orçamento para esta categoria e usuário
        $existingBudget = Budget::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->first();
    
        if ($existingBudget) {
            // Atualiza apenas o limit_amount se já existir
            $existingBudget->update(['limit_amount' => $request->limit_amount]);
            return response()->json($existingBudget, 200); // 200 OK (atualização)
        } else {
            // Cria um novo registro se não existir
            $budget = Budget::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'limit_amount' => $request->limit_amount,
            ]);
            return response()->json($budget, 201); // 201 Created (novo registro)
        }
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::where('id', $id)
                        ->where('user_id', auth()->id())
                        ->first();
                        
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }
    
        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'limit_amount' => 'sometimes|numeric|min:0',
        ]);
    
        // Se category_id foi enviado no request, verifica se já existe outro orçamento
        // com a mesma category_id para o usuário logado
        if ($request->has('category_id')) {
            $existingBudget = Budget::where('user_id', auth()->id())
                ->where('category_id', $request->category_id)
                ->where('id', '!=', $id) // Ignora o próprio orçamento que está sendo atualizado
                ->first();
    
            if ($existingBudget) {
                // Atualiza o orçamento existente e exclui o atual (para evitar duplicação)
                $existingBudget->update(['limit_amount' => $request->limit_amount ?? $budget->limit_amount]);
                $budget->delete(); // Opcional: usar soft delete se aplicável
    
                return response()->json([
                    'message' => 'Orçamento mesclado com sucesso!',
                    'budget' => $existingBudget,
                ], 200);
            }
        }
    
        // Se não houver conflito, atualiza normalmente
        $budget->update($request->only(['category_id', 'limit_amount']));
    
        return response()->json([
            'message' => 'Orçamento atualizado com sucesso!',
            'budget' => $budget,
        ], 200);
    }
    
    // Remove um orçamento do usuário logado
    public function destroy($id)
    {
        $budget = Budget::where('id', $id)
                        ->where('user_id', auth()->id())
                        ->first();
                        
        if (!$budget) {
            return response()->json(['error' => 'Orçamento não encontrado'], 404);
        }

        $budget->delete();

        return response()->json(['message' => 'Orçamento removido com sucesso']);
    }
}