<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriaMenu;
use Illuminate\Http\Request;

class CategoriaMenuController extends Controller
{
    public function index()
    {
        return CategoriaMenu::all();
    }

    public function store(Request $request)
    {
        return CategoriaMenu::create($request->all());
    }

    public function show(CategoriaMenu $categoriaMenu)
    {
        return $categoriaMenu;
    }

    public function update(Request $request, CategoriaMenu $categoriaMenu)
    {
        $categoriaMenu->update($request->all());
        return $categoriaMenu;
    }

    public function destroy(CategoriaMenu $categoriaMenu)
    {
        $categoriaMenu->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
