<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTag;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['directory', 'creator', 'tags']);

        if ($request->has('directory_id')) {
            $query->where('directory_id', $request->directory_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        // Load directory parents for full path calculation
        $documents->each(function ($document) {
            if ($document->directory) {
                $document->directory->loadMissing('parent.parent.parent.parent.parent');
                $document->directory_path = $document->directory->full_path;
            } else {
                $document->directory_path = 'Root';
            }
        });

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'directory_id' => 'required|exists:directories,id',
            'file' => 'required|file',
            'version' => 'required|string|max:255',
            'type' => 'required|in:QA,Image,Process_Chart,Presentation,General_Doc',
            'purpose' => 'required|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'in:Internal,Enterprise,Public',
        ]);

        // Dummy file handling - just store the file name
        $filePath = 'dummy/path/'.$request->file('file')->getClientOriginalName();

        $document = Document::create([
            'name' => $request->name,
            'directory_id' => $request->directory_id,
            'file_path' => $filePath,
            'version' => $request->version,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'metadata' => $request->metadata,
            'status' => 'SUCCESS', // Dummy success status
            'created_by' => $request->user()->id,
        ]);

        // Add tags if provided
        if ($request->has('tags')) {
            foreach ($request->tags as $tag) {
                DocumentTag::create([
                    'document_id' => $document->id,
                    'tag_name' => $tag,
                ]);
            }
        }

        return response()->json($document->load(['directory', 'creator', 'tags']), 201);
    }

    public function show($id)
    {
        $document = Document::with(['directory', 'creator', 'tags'])->findOrFail($id);

        return response()->json($document);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'directory_id' => 'required|exists:directories,id',
            'version' => 'required|string|max:255',
            'type' => 'required|in:QA,Image,Process_Chart,Presentation,General_Doc',
            'purpose' => 'required|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'in:Internal,Enterprise,Public',
        ]);

        $document = Document::findOrFail($id);
        $document->update($request->only([
            'name', 'directory_id', 'version', 'type', 'purpose', 'metadata',
        ]));

        // Update tags
        if ($request->has('tags')) {
            DocumentTag::where('document_id', $document->id)->delete();
            foreach ($request->tags as $tag) {
                DocumentTag::create([
                    'document_id' => $document->id,
                    'tag_name' => $tag,
                ]);
            }
        }

        return response()->json($document->load(['directory', 'creator', 'tags']));
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function process(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        // Simulate processing delay and random status
        sleep(1);
        $statuses = ['SUCCESS', 'FAILED', 'UPDATING'];
        $randomStatus = $statuses[array_rand($statuses)];

        $document->update(['status' => $randomStatus]);

        return response()->json([
            'message' => 'Document processing initiated',
            'status' => $randomStatus,
            'document' => $document->load(['directory', 'creator', 'tags']),
        ]);
    }
}
