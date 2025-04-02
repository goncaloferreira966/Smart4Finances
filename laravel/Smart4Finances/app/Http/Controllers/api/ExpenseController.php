<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Http\Request;
use App\Notifications\CustomNotification;
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
        // Log dos dados recebidos para debug
        \Log::info("Update Expense Request", $request->all());
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

        // Obtém o orçamento da categoria associada
        $budget = Budget::where('user_id', auth()->id())
            ->where('category_id', $data['category_id'])
            ->first();

        if ($budget) {
            // Obtém o nome da categoria
            $categoryName = $budget->category ? $budget->category->name : 'Desconhecida';


            // Obtém o mês atual formatado (Ex: Abril de 2025)
            $currentMonth = now()->translatedFormat('m/Y');

            // Calcula o total gasto na categoria no mês atual
            $totalSpentThisMonth = Expense::where('user_id', auth()->id())
                ->where('category_id', $data['category_id'])
                ->whereMonth('date', now()->month)
                ->sum('amount');

            // Verifica o impacto da nova despesa
            $newTotal = $totalSpentThisMonth + $data['amount'];
            $percentageUsed = ($newTotal / $budget->limit_amount) * 100;

            // Notificações de alerta
            $user = auth()->user();
            if ($percentageUsed >= 100) {
                $user->notify(new CustomNotification("🚨 Alerta: O seu orçamento para a categoria de $categoryName em $currentMonth foi excedido!"));
            } elseif ($percentageUsed >= 90) {
                $user->notify(new CustomNotification("⚠️ Atenção: Você já usou 90% ou mais do seu orçamento para a categoria de $categoryName em $currentMonth."));
            }
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

    public function update(Request $request, $id)
    {
        // Encontre a despesa
        
        $expense = Expense::find($id);
        if (!$expense) {
            return response()->json(['error' => 'Despesa não encontrada'], 404);
        }

        // Verifica se a despesa pertence ao usuário logado
        if ($expense->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        // Atualiza o upload do recibo, se houver um novo arquivo
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $expense->receipt = $path;
        }

        // Atualiza os demais campos, se presentes no request
        if ($request->has('category_id')) {
            $expense->category_id = $request->input('category_id');
        }

        if ($request->has('amount')) {
            $expense->amount = $request->input('amount');
        }

        if ($request->has('description')) {
            $expense->description = $request->input('description');
        }

        if ($request->has('date')) {
            $expense->date = $request->input('date');
        }

        // Log para verificar o objeto antes do save
        \Log::info("Expense before save", $expense->toArray());

        try {
            $expense->save();

               // Verifica se existe um orçamento para a nova categoria
        $budget = Budget::where('user_id', auth()->id())
        ->where('category_id', $expense->category_id)
        ->first();

        if ($budget) {
            // Obtém o nome da categoria
            $categoryName = $budget->category ? $budget->category->name : 'Desconhecida';

            // Obtém o mês atual formatado (Ex: 04/2025)
            $currentMonth = now()->translatedFormat('m/Y');

            // Calcula o total gasto na categoria no mês atual
            $totalSpentThisMonth = Expense::where('user_id', auth()->id())
                ->where('category_id', $expense->category_id)
                ->whereMonth('date', now()->month)
                ->sum('amount');

            // Verifica o impacto da nova despesa (considerando o valor atualizado)
            $percentageUsed = ($totalSpentThisMonth / $budget->limit_amount) * 100;

            // Notificações de alerta
            $user = auth()->user();
            if ($percentageUsed >= 100) {
                $user->notify(new CustomNotification("🚨 Alerta: O seu orçamento para a categoria de $categoryName em $currentMonth foi excedido!"));
            } elseif ($percentageUsed >= 90) {
                $user->notify(new CustomNotification("⚠️ Atenção: Você já usou 90% ou mais do seu orçamento para a categoria de $categoryName em $currentMonth."));
            }
        }

            return response()->json([
                'message' => 'Despesa atualizada com sucesso!',
                'expense' => $expense,
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Erro ao atualizar a despesa", ['error' => $e->getMessage(), 'expense' => $expense->toArray()]);
            return response()->json([
                'message' => 'Erro ao atualizar a despesa',
                'error' => $e->getMessage(),
            ], 500);
        }
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
