<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    // Lista todas as despesas do usuário autenticado (opcional)
    public function index()
    {
        // Exemplo: listar somente despesas do usuário autenticado
        $expenses = Expense::where('user_id', auth()->id())->get();
        return response()->json($expenses);
    }

    // Cria uma nova despesa
    public function store(Request $request)
    {
        // Valida que os campos 'date' e 'amount' são obrigatórios
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'date'   => 'required|date',
            'amount' => 'required|numeric',
        ]);

        // Se o campo de intervalo for "0", define como null (não recorrente)
        if ($request->input('recurring_interval') === "0") {
            $request->merge([
                'recurring_interval'      => null,
            ]);
        }

        // Associa a despesa ao usuário autenticado
        $request->merge(['user_id' => auth()->id()]);

        // Trata o upload do recibo, se enviado
        if ($request->hasFile('receipt')) {
            // Armazena o arquivo na pasta "receipts" no disco "public"
            $path = $request->file('receipt')->store('receipts', 'public');
            // Atualiza o campo receipt com o caminho do arquivo
            $request->merge(['receipt' => $path]);
        }

        $expense = Expense::create($request->all());

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
