<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index() {
        return Todo::all();
    }

    public function store(Request $request) {
        $request->validate(['title' => 'required|string|max:255']);
        return Todo::create(['title' => $request->title, 'completed' => false]);
    }

   public function update(Request $request, $id)
{
    $todo = Todo::findOrFail($id);
    $todo->update($request->all());

    return response()->json($todo);
}

    public function destroy(Todo $todo) {
        $todo->delete();
        return response()->noContent();
    }
    
}
