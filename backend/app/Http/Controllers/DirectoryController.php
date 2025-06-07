<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DirectoryController extends Controller
{
    #[OA\Get(
        path: '/directories',
        summary: 'Get directory tree',
        description: 'Retrieve hierarchical directory structure with nested children',
        security: [['bearerAuth' => []]],
        tags: ['Directories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Directory tree retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'parent_id', type: 'integer', nullable: true),
                            new OA\Property(property: 'created_by', type: 'integer'),
                            new OA\Property(property: 'children', type: 'array', items: new OA\Items(type: 'object')),
                            new OA\Property(property: 'creator', type: 'object'),
                            new OA\Property(property: 'documents', type: 'array', items: new OA\Items(type: 'object')),
                        ]
                    )
                )
            ),
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

    #[OA\Get(
        path: '/directories/{id}/contents',
        summary: 'Get directory contents',
        description: 'Get detailed contents of a specific directory including subdirectories and documents',
        security: [['bearerAuth' => []]],
        tags: ['Directories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Directory ID (use 0 for root)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ]
    )]
    public function getContents($id)
    {
        // Handle root directory case (id = 0)
        if ($id == 0) {
            $directories = Directory::with(['creator'])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();
            $documents = collect();
            $currentDirectory = null;
            $breadcrumbs = [['id' => 0, 'name' => 'Root', 'path' => '/']];
        } else {
            $currentDirectory = Directory::with(['creator', 'parent'])->findOrFail($id);

            $directories = Directory::with(['creator'])
                ->where('parent_id', $id)
                ->orderBy('name')
                ->get();

            $documents = $currentDirectory->documents()
                ->with(['creator', 'tags'])
                ->orderBy('name')
                ->get();

            // Build breadcrumbs
            $breadcrumbs = $this->buildBreadcrumbs($currentDirectory);
        }

        // Add counts to directories
        $directories = $directories->map(function ($dir) {
            $subDirCount = Directory::where('parent_id', $dir->id)->count();
            $docCount = $dir->documents()->count();
            $dir->subdirectory_count = $subDirCount;
            $dir->document_count = $docCount;
            $dir->total_count = $subDirCount + $docCount;

            return $dir;
        });

        return response()->json([
            'current_directory' => $currentDirectory,
            'directories' => $directories,
            'documents' => $documents,
            'breadcrumbs' => $breadcrumbs,
            'stats' => [
                'directory_count' => $directories->count(),
                'document_count' => $documents->count(),
                'total_count' => $directories->count() + $documents->count(),
            ],
        ]);
    }

    public function destroy($id)
    {
        $directory = Directory::findOrFail($id);
        $directory->delete();

        return response()->json(['message' => 'Directory deleted successfully']);
    }

    private function buildBreadcrumbs($directory)
    {
        $breadcrumbs = [];
        $current = $directory;

        while ($current) {
            array_unshift($breadcrumbs, [
                'id' => $current->id,
                'name' => $current->name,
                'path' => '/'.collect($breadcrumbs)->pluck('name')->prepend($current->name)->implode('/'),
            ]);
            $current = $current->parent;
        }

        // Add root at the beginning
        array_unshift($breadcrumbs, [
            'id' => 0,
            'name' => 'Root',
            'path' => '/',
        ]);

        return $breadcrumbs;
    }

    private function loadNestedChildren($directory)
    {
        $directory->children = $directory->children->map(function ($child) {
            return $this->loadNestedChildren($child);
        });

        return $directory;
    }
}
