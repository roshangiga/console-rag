<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Directory;

class DirectoryController extends Controller
{
    public function index()
    {
        $directories = Directory::with(['children', 'creator', 'documents'])
            ->whereNull('parent_id')
            ->get()
            ->map(function ($directory) {
                return $this->loadNestedChildren($directory);
            });

        return response()->json($directories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:directories,id',
        ]);

        $directory = Directory::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($directory->load('creator'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:directories,id',
        ]);

        $directory = Directory::findOrFail($id);
        $directory->update($request->only(['name', 'parent_id']));

        return response()->json($directory->load('creator'));
    }

    public function destroy($id)
    {
        $directory = Directory::findOrFail($id);
        $directory->delete();

        return response()->json(['message' => 'Directory deleted successfully']);
    }

    private function loadNestedChildren($directory)
    {
        $directory->children = $directory->children->map(function ($child) {
            return $this->loadNestedChildren($child);
        });

        return $directory;
    }
}
