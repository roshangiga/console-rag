<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get basic statistics
        $stats = [
            'total_directories' => Directory::count(),
            'total_documents' => Document::count(),
            'total_users' => User::count(),
            'recent_documents' => Document::with(['directory', 'tags'])->latest()->limit(5)->get(),
            'documents_by_type' => Document::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'documents_by_status' => Document::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
        ];

        // Get directory tree structure with counts
        $directoryTree = $this->getDirectoryTreeWithCounts();

        // Get recent activity (last 10 documents)
        $recentActivity = Document::with(['directory'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'type' => $doc->type,
                    'status' => $doc->status,
                    'directory' => $doc->directory->name ?? 'Root',
                    'created_at' => $doc->created_at,
                    'file_size_mb' => $doc->file_size ? round($doc->file_size / 1024 / 1024, 2) : 0,
                ];
            });

        return view('dashboard', compact('stats', 'directoryTree', 'recentActivity'));
    }

    private function getDirectoryTreeWithCounts($parentId = null, $level = 0)
    {
        $directories = Directory::where('parent_id', $parentId)
            ->with(['children'])
            ->get();

        $tree = [];
        foreach ($directories as $directory) {
            $documentCount = Document::where('directory_id', $directory->id)->count();
            $childTree = $this->getDirectoryTreeWithCounts($directory->id, $level + 1);

            $totalDocuments = $documentCount + collect($childTree)->sum('total_documents');

            $tree[] = [
                'id' => $directory->id,
                'name' => $directory->name,
                'level' => $level,
                'document_count' => $documentCount,
                'total_documents' => $totalDocuments,
                'children_count' => count($childTree),
                'children' => $childTree,
                'created_at' => $directory->created_at,
            ];
        }

        return $tree;
    }
}
