<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Directory;
use OpenApi\Attributes as OA;

class DirectoryController extends Controller
{
    #[OA\Get(
        path: "/directories",
        summary: "Get directory tree",
        description: "Retrieve hierarchical directory structure with nested children",
        security: [["bearerAuth" => []]],
        tags: ["Directories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Directory tree retrieved successfully",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "name", type: "string"),
                            new OA\Property(property: "parent_id", type: "integer", nullable: true),
                            new OA\Property(property: "created_by", type: "integer"),
                            new OA\Property(property: "children", type: "array", items: new OA\Items(type: "object")),
                            new OA\Property(property: "creator", type: "object"),
                            new OA\Property(property: "documents", type: "array", items: new OA\Items(type: "object"))
                        ]
                    )
                )
            )
        ]
    )]
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
