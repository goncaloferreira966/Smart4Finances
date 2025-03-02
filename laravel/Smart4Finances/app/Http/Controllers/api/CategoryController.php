<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Lista todas as categorias
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Cria uma nova categoria
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::create($request->only('name'));

        return response()->json($category, 201);
    }

    // Exibe os detalhes de uma categoria
    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }
        return response()->json($category);
    }

    // Atualiza uma categoria existente
    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update($request->only('name'));

        return response()->json($category);
    }

    // Remove uma categoria
    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Categoria não encontrada'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Categoria removida com sucesso']);
    }
}
