<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use App\Models\Document;
use App\Models\DocumentTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class DocumentController extends Controller
{
    #[OA\Get(
        path: '/documents',
        summary: 'Get documents list',
        description: 'Retrieve all documents with optional filtering',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(
                name: 'directory_id',
                description: 'Filter by directory ID',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'type',
                description: 'Filter by document type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['QA', 'Image', 'Process_Chart', 'Presentation', 'General_Doc'])
            ),
            new OA\Parameter(
                name: 'status',
                description: 'Filter by status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['SUCCESS', 'FAILED', 'UPDATING'])
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Search by document name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Documents retrieved successfully',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'name', type: 'string'),
                            new OA\Property(property: 'file_path', type: 'string'),
                            new OA\Property(property: 'version', type: 'string'),
                            new OA\Property(property: 'type', type: 'string'),
                            new OA\Property(property: 'purpose', type: 'string'),
                            new OA\Property(property: 'status', type: 'string'),
                            new OA\Property(property: 'directory', type: 'object'),
                            new OA\Property(property: 'creator', type: 'object'),
                            new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'object')),
                        ]
                    )
                )
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/documents',
        summary: 'Upload a new document',
        description: 'Upload a document file with metadata and tags',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'directory_id', 'file', 'type'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', description: 'Document name'),
                        new OA\Property(property: 'directory_id', type: 'integer', description: 'Directory ID'),
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'The file to upload'),
                        new OA\Property(property: 'version', type: 'string', description: 'Document version'),
                        new OA\Property(property: 'type', type: 'string', enum: ['QA', 'Image', 'Process_Chart', 'Presentation', 'General_Doc']),
                        new OA\Property(property: 'purpose', type: 'string', description: 'Document purpose'),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'string', enum: ['Internal', 'Enterprise', 'Public'])),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Document uploaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'name', type: 'string'),
                        new OA\Property(property: 'file_path', type: 'string'),
                        new OA\Property(property: 'file_size', type: 'integer'),
                        new OA\Property(property: 'version', type: 'string'),
                        new OA\Property(property: 'type', type: 'string'),
                        new OA\Property(property: 'purpose', type: 'string'),
                        new OA\Property(property: 'status', type: 'string'),
                        new OA\Property(property: 'directory', type: 'object'),
                        new OA\Property(property: 'creator', type: 'object'),
                        new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'directory_id' => 'required|integer',
            'file' => 'required|file|max:51200', // Max 50MB
            'version' => 'nullable|string|max:255',
            'type' => 'required|in:QA,Image,Process_Chart,Presentation,General_Doc',
            'purpose' => 'nullable|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'in:Internal,Enterprise,Public',
        ]);

        // Handle file upload
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        // Create directory path based on directory ID
        $directoryId = $request->directory_id;
        $directory = $directoryId != 0 ? \App\Models\Directory::find($directoryId) : null;
        $directoryPath = $this->getDirectoryPath($directory);

        // Generate unique filename to avoid conflicts
        $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

        // Store the file
        $storagePath = 'documents'.$directoryPath;
        $filePath = $file->storeAs($storagePath, $filename, 'local');

        // Get file size
        $fileSize = $file->getSize();

        $document = Document::create([
            'name' => $request->name,
            'directory_id' => $directoryId == 0 ? null : $directoryId, // Use null for root
            'file_path' => str_replace('documents', '', $filePath), // Remove 'documents' prefix
            'file_size' => $fileSize,
            'version' => $request->version ?: '1.0',
            'type' => $request->type,
            'purpose' => $request->purpose ?: '',
            'metadata' => $request->metadata,
            'status' => 'SUCCESS',
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
            'directory_id' => 'nullable|integer', // Allow null for root directory
            'version' => 'required|string|max:255',
            'type' => 'required|in:QA,Image,Process_Chart,Presentation,General_Doc',
            'purpose' => 'required|string',
            'metadata' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'in:Internal,Enterprise,Public',
        ]);

        $document = Document::findOrFail($id);
        $oldDirectoryId = $document->directory_id;
        $newDirectoryId = $request->directory_id == 0 ? null : $request->directory_id;

        // Check if directory changed - if so, we need to move the file physically
        if ($oldDirectoryId != $newDirectoryId && $document->file_path) {
            $this->moveDocumentFile($document, $newDirectoryId);
        }

        $document->update([
            'name' => $request->name,
            'directory_id' => $newDirectoryId,
            'version' => $request->version,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'metadata' => $request->metadata,
        ]);

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

    #[OA\Delete(
        path: '/documents/{id}',
        summary: 'Delete document',
        description: 'Delete a document and its associated file from storage',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Document ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Document deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Document deleted successfully'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Document not found'
            ),
        ]
    )]
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Delete the physical file from storage if it exists
        if ($document->file_path) {
            $fullPath = 'documents'.$document->file_path;

            if (Storage::disk('local')->exists($fullPath)) {
                Storage::disk('local')->delete($fullPath);
                \Log::info("Deleted file: {$fullPath}");
            } else {
                \Log::warning("File not found for deletion: {$fullPath}");
            }
        }

        // Delete the database record
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    #[OA\Post(
        path: '/documents/{id}/process',
        summary: 'Process document',
        description: 'Initiate document processing with simulated delay and random status',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Document ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Document processing initiated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Document processing initiated'),
                        new OA\Property(property: 'status', type: 'string', enum: ['SUCCESS', 'FAILED', 'UPDATING']),
                        new OA\Property(property: 'document', type: 'object'),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Document not found'
            ),
        ]
    )]
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

    #[OA\Get(
        path: '/documents/{id}/download',
        summary: 'Download document file',
        description: 'Download or preview a document file by ID. Returns the actual file content with appropriate headers for browser viewing or downloading.',
        security: [['bearerAuth' => []]],
        tags: ['Documents'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Document ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'File downloaded successfully',
                content: new OA\MediaType(
                    mediaType: 'application/octet-stream',
                    schema: new OA\Schema(type: 'string', format: 'binary')
                ),
                headers: [
                    new OA\Header(
                        header: 'Content-Type',
                        description: 'MIME type of the file',
                        schema: new OA\Schema(type: 'string')
                    ),
                    new OA\Header(
                        header: 'Content-Disposition',
                        description: 'File disposition (inline for preview, attachment for download)',
                        schema: new OA\Schema(type: 'string')
                    ),
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Document or file not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'File not found'),
                    ]
                )
            ),
        ]
    )]
    public function download($id)
    {
        $document = Document::findOrFail($id);

        if (! $document->file_path) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $fullPath = 'documents'.$document->file_path;

        if (! Storage::disk('local')->exists($fullPath)) {
            return response()->json(['error' => 'File not found on disk'], 404);
        }

        $filename = basename($document->file_path);
        $mimeType = Storage::disk('local')->mimeType($fullPath);

        return Storage::disk('local')->response($fullPath, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'public, max-age=86400', // Cache for 1 day
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
        ]);
    }

    /**
     * Get the full directory path for a given directory
     * Creates storage-safe path by converting spaces to hyphens and making lowercase
     */
    private function getDirectoryPath($directory)
    {
        if (! $directory) {
            return '/';
        }

        $path = '/'.$this->sanitizeDirectoryName($directory->name);
        $parent = $directory->parent;

        while ($parent) {
            $path = '/'.$this->sanitizeDirectoryName($parent->name).$path;
            $parent = $parent->parent;
        }

        return $path;
    }

    /**
     * Sanitize directory name for file system storage
     * Converts "Innovation Projects" to "innovation-projects"
     */
    private function sanitizeDirectoryName($name)
    {
        return strtolower(preg_replace('/\s+/', '-', trim($name)));
    }

    /**
     * Move a document file to a new directory in storage
     */
    private function moveDocumentFile(Document $document, $newDirectoryId)
    {
        $oldFilePath = 'documents'.$document->file_path;

        // Get new directory and build new path
        $newDirectory = $newDirectoryId ? Directory::find($newDirectoryId) : null;
        $newDirectoryPath = $this->getDirectoryPath($newDirectory);
        $filename = basename($document->file_path);
        $newFilePath = 'documents'.$newDirectoryPath.'/'.$filename;

        // Only move if paths are different
        if ($oldFilePath !== $newFilePath) {
            // Check if old file exists
            if (! Storage::disk('local')->exists($oldFilePath)) {
                \Log::warning("File to move does not exist: {$oldFilePath}");

                return false;
            }

            // Create new directory if it doesn't exist
            $newDir = dirname($newFilePath);
            if (! Storage::disk('local')->exists($newDir)) {
                Storage::disk('local')->makeDirectory($newDir);
                \Log::info("Created directory: {$newDir}");
            }

            // Move the file
            if (Storage::disk('local')->move($oldFilePath, $newFilePath)) {
                // Update the document's file_path in database
                $document->file_path = str_replace('documents', '', $newFilePath);
                \Log::info("Moved file from {$oldFilePath} to {$newFilePath}");

                // Clean up empty directories
                $this->cleanupEmptyDirectory(dirname($oldFilePath));

                return true;
            } else {
                \Log::error("Failed to move file from {$oldFilePath} to {$newFilePath}");

                return false;
            }
        }

        return true;
    }

    /**
     * Clean up empty directories after moving files
     */
    private function cleanupEmptyDirectory($directory)
    {
        if (! Storage::disk('local')->exists($directory)) {
            return;
        }

        $files = Storage::disk('local')->files($directory);
        $subdirs = Storage::disk('local')->directories($directory);

        // If directory is empty, delete it
        if (empty($files) && empty($subdirs)) {
            Storage::disk('local')->deleteDirectory($directory);
            \Log::info("Removed empty directory: {$directory}");

            // Recursively check parent directories (but don't go above documents/)
            $parentDir = dirname($directory);
            if ($parentDir !== $directory && $parentDir !== 'documents') {
                $this->cleanupEmptyDirectory($parentDir);
            }
        }
    }
}
