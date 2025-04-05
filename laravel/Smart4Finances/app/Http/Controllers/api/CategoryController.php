<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lista as categorias do usuário autenticado e categorias gerais (user_id = null)
    public function index()
    {
        // Obtém as categorias do usuário
        $userCategories = auth()->user()->categories;
        
        // Obtém as categorias gerais (user_id = null)
        $generalCategories = Category::whereNull('user_id')->get();
        
        // Combina as coleções
        $categories = $userCategories->concat($generalCategories);
        
        return response()->json($categories);
    }

    // Cria uma nova categoria associada ao usuário autenticado
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = new Category($request->only('name'));
        $category->user_id = auth()->id();
        $category->save();

        return response()->json($category, 201);
    }

    // Exibe os detalhes de uma categoria do usuário ou categoria geral
    public function show($id)
    {
        $category = Category::where('id', $id)
                            ->where(function($query) {
                                $query->where('user_id', auth()->id())
                                     ->orWhereNull('user_id');
                            })
                            ->first();
                            
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }
        
        return response()->json($category);
    }

    // Atualiza uma categoria existente do usuário (não permite editar categorias gerais)
    public function update(Request $request, $id)
    {
        $category = Category::where('id', $id)
                            ->where('user_id', auth()->id())
                            ->first();
                            
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada ou não pode ser editada'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update($request->only('name'));

        return response()->json($category);
    }

    // Remove uma categoria do usuário (não permite remover categorias gerais)
    public function destroy($id)
    {
        $category = Category::where('id', $id)
                            ->where('user_id', auth()->id())
                            ->first();
                            
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada ou não pode ser removida'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Categoria removida com sucesso']);
    }
}
